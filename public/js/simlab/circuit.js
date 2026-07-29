/**
 * PembdaHUB SimLab - Circuit Workspace Manager v4.0
 * Fitur:
 * - Waypoint-based wire routing (setiap segmen bisa digeser H & V)
 * - Workspace putih dengan grid SVG konfigurabel & snap-to-grid
 * - Nama komponen editable oleh user (double-click)
 * - Panel daftar komponen & koneksi (collapsible)
 * - Right-click context menu warna & tipe kabel
 * - Indikator pin di kedua ujung kabel
 * - Rotasi & zoom komponen
 */

window.SimLabCircuit = {
    components: [],
    wires: [],
    selectedWireColor: "#ef4444",
    selectedWireId: null,
    connectingPin: null,
    compCounter: 0,
    zoomLevel: 1.0,
    wireStyleMode: "orthogonal",
    activeWireContext: null,

    // Grid Settings
    gridSize: 20,
    gridVisible: true,
    snapEnabled: true,

    init: function() {
        this.bindEvents();
        this.initContextMenu();
        this.drawGrid();
        this.renderAll();
        this.updateConnectionPanel();
    },

    // Snap value to nearest grid multiple
    snap: function(val) {
        if (!this.snapEnabled || this.gridSize <= 1) return val;
        return Math.round(val / this.gridSize) * this.gridSize;
    },

    bindEvents: function() {
        const self = this;

        // Wire Color Buttons
        document.querySelectorAll('.wire-color-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.wire-color-btn').forEach(b => b.classList.remove('ring-2', 'ring-emerald-400'));
                this.classList.add('ring-2', 'ring-emerald-400');
                self.selectedWireColor = this.dataset.color || '#ef4444';
            });
        });

        // Add Component Buttons
        document.querySelectorAll('.add-comp-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                self.addComponent(type);
            });
        });

        // Toggle Wire Style Mode
        document.getElementById('btnToggleWireStyle')?.addEventListener('click', function() {
            self.wireStyleMode = self.wireStyleMode === 'orthogonal' ? 'curved' : 'orthogonal';
            document.getElementById('wireStyleLabel').textContent =
                self.wireStyleMode === 'orthogonal' ? 'Kabel: Lurus 90°' : 'Kabel: Lengkung';
            self.renderWires();
        });

        // Clear Buttons
        document.getElementById('btnClearWires')?.addEventListener('click', function() {
            if (confirm('Hapus semua sambungan kabel?')) {
                self.wires = [];
                self.renderWires();
                self.updateConnectionPanel();
            }
        });
        document.getElementById('btnClearCanvas')?.addEventListener('click', function() {
            if (confirm('Kosongkan canvas (hapus semua komponen & kabel)?')) {
                self.components = [];
                self.wires = [];
                self.renderAll();
                self.updateConnectionPanel();
            }
        });

        // Zoom Buttons
        document.getElementById('btnZoomIn')?.addEventListener('click', function() {
            self.zoomLevel = Math.min(3.0, self.zoomLevel + 0.15);
            self.applyZoom();
        });
        document.getElementById('btnZoomOut')?.addEventListener('click', function() {
            self.zoomLevel = Math.max(0.3, self.zoomLevel - 0.15);
            self.applyZoom();
        });

        // Mouse Wheel Zoom
        const canvasContainer = document.getElementById('circuitCanvasContainer');
        if (canvasContainer) {
            canvasContainer.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                self.zoomLevel = Math.min(3.0, Math.max(0.3, self.zoomLevel + delta));
                self.applyZoom();
            }, { passive: false });
        }

        // Grid Controls
        document.getElementById('gridSizeSelect')?.addEventListener('change', function() {
            self.gridSize = parseInt(this.value) || 20;
            self.drawGrid();
        });
        document.getElementById('btnToggleGrid')?.addEventListener('click', function() {
            self.gridVisible = !self.gridVisible;
            self.drawGrid();
            this.classList.toggle('text-emerald-400', self.gridVisible);
            this.classList.toggle('text-gray-500', !self.gridVisible);
        });
        document.getElementById('btnToggleSnap')?.addEventListener('click', function() {
            self.snapEnabled = !self.snapEnabled;
            this.classList.toggle('text-emerald-400', self.snapEnabled);
            this.classList.toggle('text-gray-500', !self.snapEnabled);
            const label = this.querySelector('span');
            if (label) label.textContent = self.snapEnabled ? 'Snap: ON' : 'Snap: OFF';
        });

        // Toggle Left Sidebar (Collapse/Expand)
        document.getElementById('btnToggleSidebar')?.addEventListener('click', function() {
            const sidebar = document.getElementById('leftSidebar');
            const icon = this.querySelector('i');
            if (!sidebar) return;
            const isCollapsed = sidebar.classList.contains('w-0');
            if (isCollapsed) {
                sidebar.classList.remove('w-0');
                sidebar.classList.add('w-64');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-0');
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }
        });

        // Wire Preview on Mouse Move
        const svg = document.getElementById('circuitSvg');
        svg.addEventListener('mousemove', function(e) {
            if (self.connectingPin) {
                const rect = svg.getBoundingClientRect();
                const mouseX = (e.clientX - rect.left) / self.zoomLevel;
                const mouseY = (e.clientY - rect.top) / self.zoomLevel;
                const startPos = self.getPinPos(self.connectingPin.compId, self.connectingPin.pinId);
                if (startPos) {
                    const midX = startPos.x + (mouseX - startPos.x) / 2;
                    const d = `M ${startPos.x} ${startPos.y} L ${midX} ${startPos.y} L ${midX} ${mouseY} L ${mouseX} ${mouseY}`;
                    const tempPath = document.getElementById('tempWire');
                    tempPath.setAttribute('d', d);
                    tempPath.setAttribute('stroke', self.selectedWireColor);
                    tempPath.classList.remove('hidden');
                }
            }
        });

        // Click outside cancels wire drawing, deselects wire, and hides context menu
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('wireContextMenu');
            if (menu && !menu.contains(e.target)) menu.classList.add('hidden');

            const isWireOrMenu = (e.target.closest && (e.target.closest('#wiresGroup') || e.target.closest('#wireContextMenu')));
            if (!isWireOrMenu && self.selectedWireId !== null) {
                self.selectedWireId = null;
                self.renderWires();
            }

            if (e.target.id === 'circuitCanvasContainer' || e.target.id === 'circuitSvg') {
                if (self.connectingPin) {
                    self.connectingPin = null;
                    document.getElementById('tempWire').classList.add('hidden');
                    self.renderComponents();
                }
            }
        });
    },

    // ═══════════════════════════════════════════════════════════════
    // Grid System
    // ═══════════════════════════════════════════════════════════════

    drawGrid: function() {
        const gridGroup = document.getElementById('gridGroup');
        if (!gridGroup) return;
        gridGroup.innerHTML = '';
        if (!this.gridVisible) return;

        const W = 3000, H = 2000;
        const g = this.gridSize;

        // Minor grid lines
        for (let x = 0; x <= W; x += g) {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x); line.setAttribute('y1', 0);
            line.setAttribute('x2', x); line.setAttribute('y2', H);
            line.setAttribute('stroke', (x % (g * 5) === 0) ? '#cbd5e1' : '#e2e8f0');
            line.setAttribute('stroke-width', (x % (g * 5) === 0) ? '0.8' : '0.4');
            gridGroup.appendChild(line);
        }
        for (let y = 0; y <= H; y += g) {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', 0); line.setAttribute('y1', y);
            line.setAttribute('x2', W); line.setAttribute('y2', y);
            line.setAttribute('stroke', (y % (g * 5) === 0) ? '#cbd5e1' : '#e2e8f0');
            line.setAttribute('stroke-width', (y % (g * 5) === 0) ? '0.8' : '0.4');
            gridGroup.appendChild(line);
        }
    },

    // ═══════════════════════════════════════════════════════════════
    // Context Menu (Right-Click on Wire)
    // ═══════════════════════════════════════════════════════════════

    initContextMenu: function() {
        if (document.getElementById('wireContextMenu')) return;
        const menu = document.createElement('div');
        menu.id = 'wireContextMenu';
        menu.className = 'fixed hidden z-50 bg-gray-900/95 border border-gray-700/90 rounded-xl shadow-2xl p-2.5 text-xs text-gray-200 backdrop-blur-md w-52 space-y-2 select-none';
        menu.innerHTML = `
            <div class="font-bold text-gray-400 border-b border-gray-800 pb-1 text-[11px] flex justify-between items-center">
                <span>PENGATURAN KABEL</span>
                <span id="ctxWirePinLabel" class="text-emerald-400 font-mono text-[10px]"></span>
            </div>
            <div>
                <label class="text-[10px] text-gray-400 block mb-1">Warna Kabel:</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button onclick="SimLabCircuit.setCtxWireColor('#ef4444')" class="h-6 rounded bg-red-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#22c55e')" class="h-6 rounded bg-emerald-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#3b82f6')" class="h-6 rounded bg-blue-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#eab308')" class="h-6 rounded bg-yellow-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#f97316')" class="h-6 rounded bg-orange-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#a855f7')" class="h-6 rounded bg-purple-500 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#f8fafc')" class="h-6 rounded bg-slate-100 hover:scale-105 border border-white/20"></button>
                    <button onclick="SimLabCircuit.setCtxWireColor('#18181b')" class="h-6 rounded bg-zinc-900 hover:scale-105 border border-white/20"></button>
                </div>
            </div>
            <div>
                <label class="text-[10px] text-gray-400 block mb-1">Tipe Garis:</label>
                <div class="grid grid-cols-3 gap-1 text-[10px]">
                    <button onclick="SimLabCircuit.setCtxWireStyle('orthogonal')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded border border-gray-700">Lurus 90°</button>
                    <button onclick="SimLabCircuit.setCtxWireStyle('curved')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded border border-gray-700">Lengkung</button>
                    <button onclick="SimLabCircuit.setCtxWireStyle('dashed')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded border border-gray-700">Putus</button>
                </div>
            </div>
            <button onclick="SimLabCircuit.deleteCtxWire()" class="w-full py-1.5 bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white rounded font-bold border border-red-500/40">✕ Hapus Kabel</button>
        `;
        document.body.appendChild(menu);
    },
    showContextMenu: function(e, wire) {
        e.preventDefault(); e.stopPropagation();
        this.activeWireContext = wire;
        this.selectedWireId = wire.id;
        this.renderWires();
        const menu = document.getElementById('wireContextMenu');
        if (menu) {
            menu.style.left = e.clientX + 'px';
            menu.style.top = e.clientY + 'px';
            menu.classList.remove('hidden');
            const label = document.getElementById('ctxWirePinLabel');
            if (label) label.textContent = wire.fromPin + ' → ' + wire.toPin;
        }
    },
    setCtxWireColor: function(c) { if (this.activeWireContext) { this.activeWireContext.color = c; this.renderWires(); document.getElementById('wireContextMenu')?.classList.add('hidden'); } },
    setCtxWireStyle: function(s) { if (this.activeWireContext) { this.activeWireContext.style = s; this.renderWires(); document.getElementById('wireContextMenu')?.classList.add('hidden'); } },
    deleteCtxWire: function() { if (this.activeWireContext) { this.wires = this.wires.filter(w => w.id !== this.activeWireContext.id); if (this.selectedWireId === this.activeWireContext.id) this.selectedWireId = null; this.renderWires(); this.updateConnectionPanel(); document.getElementById('wireContextMenu')?.classList.add('hidden'); } },

    applyZoom: function() {
        const svg = document.getElementById('circuitSvg');
        const layer = document.getElementById('componentsLayer');
        svg.style.transform = `scale(${this.zoomLevel})`;
        svg.style.transformOrigin = '0 0';
        layer.style.transform = `scale(${this.zoomLevel})`;
        layer.style.transformOrigin = '0 0';
    },

    // ═══════════════════════════════════════════════════════════════
    // Component Management
    // ═══════════════════════════════════════════════════════════════

    addComponent: function(type, x, y, savedId, savedState, savedLabel) {
        const def = window.SimLabComponents[type];
        if (!def) {
            console.warn('Komponen "' + type + '" belum didefinisikan di components.js');
            alert('Komponen "' + type + '" belum tersedia. Akan ditambahkan di versi mendatang.');
            return null;
        }
        this.compCounter++;
        x = x || 100 + (this.components.length * 30) % 300;
        y = y || 100 + (this.components.length * 20) % 200;
        x = this.snap(x);
        y = this.snap(y);

        const id = savedId || (type + '_' + this.compCounter + '_' + Math.random().toString(36).substr(2, 4));
        const comp = { id: id, type: type, x: x, y: y, scale: 1.0, rotation: 0, label: savedLabel || def.name, state: savedState || {} };
        this.components.push(comp);
        this.renderComponents();
        this.updateConnectionPanel();

        // Auto connect schematic wires & generate code if pulled manually
        if (!savedId) {
            this.autoConnectComponentWires(comp);
            if (window.SimLabEngine && window.SimLabEngine.autoCodeEnabled) {
                window.SimLabEngine.generateSmartCode();
            }
        }

        return comp;
    },

    connectWire: function(fromCompId, fromPinId, toCompId, toPinId, color) {
        // Prevent duplicate wire
        const exists = this.wires.some(w => 
            (w.fromComp === fromCompId && w.fromPin === fromPinId && w.toComp === toCompId && w.toPin === toPinId) ||
            (w.fromComp === toCompId && w.fromPin === toPinId && w.toComp === fromCompId && w.toPin === fromPinId)
        );
        if (exists) return;

        const pos1 = this.getPinPos(fromCompId, fromPinId);
        const pos2 = this.getPinPos(toCompId, toPinId);

        let wps = [];
        if (pos1 && pos2) {
            const midX = this.snap(pos1.x + (pos2.x - pos1.x) / 2);
            wps = [
                { x: midX, y: pos1.y },
                { x: midX, y: pos2.y }
            ];
        }

        this.wires.push({
            id: 'wire_' + Math.random().toString(36).substr(2, 6),
            fromComp: fromCompId, fromPin: fromPinId,
            toComp: toCompId, toPin: toPinId,
            color: color || '#10b981', style: 'orthogonal',
            waypoints: wps
        });
        this.renderWires();
        this.updateConnectionPanel();
    },

    autoConnectComponentWires: function(comp) {
        const uno = this.components.find(c => c.type === 'uno' || c.type === 'nano' || c.type === 'esp32');
        if (!uno) return;

        const unoId = uno.id;
        const compId = comp.id;

        switch (comp.type) {
            case 'led_red':
                this.connectWire(compId, 'ANODE', unoId, 'D13', '#ef4444');
                this.connectWire(compId, 'CATHODE', unoId, 'GND_1', '#1e293b');
                break;
            case 'led_green':
                this.connectWire(compId, 'ANODE', unoId, 'D13', '#10b981');
                this.connectWire(compId, 'CATHODE', unoId, 'GND_1', '#1e293b');
                break;
            case 'led_yellow':
                this.connectWire(compId, 'ANODE', unoId, 'D13', '#eab308');
                this.connectWire(compId, 'CATHODE', unoId, 'GND_1', '#1e293b');
                break;
            case 'led_white':
                this.connectWire(compId, 'ANODE', unoId, 'D13', '#f8fafc');
                this.connectWire(compId, 'CATHODE', unoId, 'GND_1', '#1e293b');
                break;

            case 'motor_dc':
                this.connectWire(compId, 'MOTOR_A', unoId, 'D3', '#3b82f6');
                this.connectWire(compId, 'MOTOR_B', unoId, 'GND_1', '#1e293b');
                break;

            case 'l298n':
                this.connectWire(compId, 'IN1', unoId, 'D3', '#3b82f6');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                this.connectWire(compId, 'V5', unoId, '5V', '#ef4444');
                break;

            case 'servo':
                this.connectWire(compId, 'PWM', unoId, 'D9', '#f59e0b');
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'hc_sr04':
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'TRIG', unoId, 'D2', '#10b981');
                this.connectWire(compId, 'ECHO', unoId, 'D3', '#3b82f6');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'dht11':
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'DATA', unoId, 'D4', '#8b5cf6');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'lcd1602':
            case 'lcd2004':
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'SDA', unoId, 'A4', '#3b82f6');
                this.connectWire(compId, 'SCL', unoId, 'A5', '#f59e0b');
                break;

            case 'relay':
                this.connectWire(compId, 'IN', unoId, 'D7', '#ec4899');
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'pir':
                this.connectWire(compId, 'VCC', unoId, '5V', '#ef4444');
                this.connectWire(compId, 'OUT', unoId, 'D2', '#10b981');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'speaker':
                this.connectWire(compId, 'SIGNAL', unoId, 'D8', '#06b6d4');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                break;

            case 'ldr':
                this.connectWire(compId, 'PIN_1', unoId, 'A0', '#eab308');
                this.connectWire(compId, 'PIN_2', unoId, 'GND_1', '#1e293b');
                break;

            case 'rc522':
                this.connectWire(compId, 'VCC', unoId, '3V3', '#ef4444');
                this.connectWire(compId, 'RST', unoId, 'D9', '#f59e0b');
                this.connectWire(compId, 'GND', unoId, 'GND_1', '#1e293b');
                this.connectWire(compId, 'MISO', unoId, 'D12', '#3b82f6');
                this.connectWire(compId, 'MOSI', unoId, 'D11', '#10b981');
                this.connectWire(compId, 'SCK', unoId, 'D13', '#8b5cf6');
                this.connectWire(compId, 'SDA', unoId, 'D10', '#ec4899');
                break;
        }
    },

    removeComponent: function(id) {
        this.components = this.components.filter(c => c.id !== id);
        this.wires = this.wires.filter(w => w.fromComp !== id && w.toComp !== id);
        this.renderAll();
        this.updateConnectionPanel();

        if (window.SimLabEngine && window.SimLabEngine.autoCodeEnabled) {
            window.SimLabEngine.generateSmartCode();
        }
    },

    renderAll: function() {
        this.renderComponents();
        this.renderWires();
    },

    renderComponents: function() {
        const layer = document.getElementById('componentsLayer');
        layer.innerHTML = '';
        const self = this;

        this.components.forEach(comp => {
            const def = window.SimLabComponents[comp.type];
            if (!def) return;
            const scale = comp.scale || 1.0;
            const rotation = comp.rotation || 0;
            const headerWidth = Math.max(145, def.width + 16);

            const div = document.createElement('div');
            div.id = 'comp_' + comp.id;
            div.className = 'absolute pointer-events-auto rounded-xl p-2 border border-gray-300 shadow-lg group hover:border-emerald-500 transition-all';
            div.style.left = comp.x + 'px';
            div.style.top = comp.y + 'px';
            div.style.width = headerWidth + 'px';
            div.style.transform = `scale(${scale}) rotate(${rotation}deg)`;
            div.style.transformOrigin = 'center center';
            div.style.zIndex = '30';
            div.style.backgroundColor = 'rgba(255,255,255,0.95)';

            let html = `
            <div class="flex items-center justify-between mb-1 handle cursor-move text-[10px] text-gray-500 border-b border-gray-200 pb-1 select-none w-full">
                <span class="font-bold text-gray-800 truncate pr-1 text-[11px] comp-label cursor-text" ondblclick="SimLabCircuit.editLabel('${comp.id}', this)" title="Double-click untuk edit nama">${comp.label || def.name}</span>
                <div class="flex items-center space-x-1 shrink-0">
                    <button onclick="SimLabCircuit.rotateComponent('${comp.id}')" class="text-cyan-600 hover:text-cyan-500 font-black px-1 py-0.5 text-xs hover:bg-gray-100 rounded" title="Rotasi 90°">⟳</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', 0.15)" class="text-emerald-600 hover:text-emerald-500 font-black px-1 py-0.5 text-xs hover:bg-gray-100 rounded" title="Perbesar">+</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', -0.15)" class="text-amber-600 hover:text-amber-500 font-black px-1 py-0.5 text-xs hover:bg-gray-100 rounded" title="Perkecil">-</button>
                    <button onclick="SimLabCircuit.removeComponent('${comp.id}')" class="text-red-500 hover:text-red-400 font-black px-1 py-0.5 text-xs hover:bg-gray-100 rounded" title="Hapus">✕</button>
                </div>
            </div>
            <div class="relative" style="width:${def.width}px;height:${def.height}px;">
                <svg width="${def.width}" height="${def.height}" viewBox="0 0 ${def.width} ${def.height}">
                    ${def.svg(comp)}`;

            def.pins.forEach(pin => {
                const isSelected = self.connectingPin && self.connectingPin.compId === comp.id && self.connectingPin.pinId === pin.id;
                html += `
                <g class="pin-hover cursor-pointer" onclick="SimLabCircuit.onPinClick('${comp.id}','${pin.id}')">
                    <circle cx="${pin.x}" cy="${pin.y}" r="${isSelected ? 8 : 6}" fill="${isSelected ? '#f59e0b' : '#10b981'}" stroke="#fff" stroke-width="${isSelected ? 2.5 : 1.5}"/>
                    <circle cx="${pin.x}" cy="${pin.y}" r="2" fill="#000"/>
                    <title>${pin.label} (${pin.type})</title>
                </g>`;
            });

            html += `</svg></div>`;
            if (def.controls) html += def.controls(comp);
            div.innerHTML = html;
            layer.appendChild(div);
            self.makeDraggable(div, comp);
        });
    },

    editLabel: function(compId, el) {
        const comp = this.components.find(c => c.id === compId);
        if (!comp) return;
        const oldLabel = comp.label || '';
        const input = document.createElement('input');
        input.type = 'text';
        input.value = oldLabel;
        input.className = 'bg-white text-gray-900 text-[11px] font-bold border border-emerald-400 rounded px-1 py-0 w-full focus:outline-none';
        input.style.minWidth = '60px';
        el.replaceWith(input);
        input.focus();
        input.select();

        const self = this;
        function save() {
            comp.label = input.value.trim() || oldLabel;
            self.renderComponents();
            self.updateConnectionPanel();
        }
        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e) { if (e.key === 'Enter') input.blur(); if (e.key === 'Escape') { input.value = oldLabel; input.blur(); } });
    },

    rotateComponent: function(id) {
        const comp = this.components.find(c => c.id === id);
        if (comp) { comp.rotation = ((comp.rotation || 0) + 90) % 360; this.renderComponents(); this.renderWires(); }
    },
    scaleComponent: function(id, delta) {
        const comp = this.components.find(c => c.id === id);
        if (comp) {
            const currentScale = comp.scale || 1.0;
            const newScale = Math.max(0.4, Math.min(3.0, Math.round((currentScale + delta) * 100) / 100));
            comp.scale = newScale;
            this.renderComponents();
            this.renderWires();
        }
    },

    makeDraggable: function(el, comp) {
        const self = this;
        let startMouseX, startMouseY, startCompX, startCompY;
        const handle = el.querySelector('.handle') || el;

        handle.onmousedown = function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button') || e.target.tagName === 'INPUT') return;
            e.preventDefault(); e.stopPropagation();
            startMouseX = e.clientX; startMouseY = e.clientY;
            startCompX = comp.x; startCompY = comp.y;
            window.addEventListener('mousemove', drag);
            window.addEventListener('mouseup', stop);
        };
        function drag(e) {
            e.preventDefault();
            const dx = (e.clientX - startMouseX) / self.zoomLevel;
            const dy = (e.clientY - startMouseY) / self.zoomLevel;
            comp.x = self.snap(Math.max(0, startCompX + dx));
            comp.y = self.snap(Math.max(0, startCompY + dy));
            el.style.left = comp.x + 'px';
            el.style.top = comp.y + 'px';
            self.renderWires();
        }
        function stop() { window.removeEventListener('mousemove', drag); window.removeEventListener('mouseup', stop); }
    },

    // ═══════════════════════════════════════════════════════════════
    // Pin Click & Wire Connection
    // ═══════════════════════════════════════════════════════════════

    onPinClick: function(compId, pinId) {
        if (!this.connectingPin) {
            this.connectingPin = { compId: compId, pinId: pinId };
            this.renderComponents();
        } else {
            if (this.connectingPin.compId !== compId || this.connectingPin.pinId !== pinId) {
                const pos1 = this.getPinPos(this.connectingPin.compId, this.connectingPin.pinId);
                const pos2 = this.getPinPos(compId, pinId);

                // Generate 2 default waypoints for clean 90° routing
                let wps = [];
                if (pos1 && pos2) {
                    const midX = this.snap(pos1.x + (pos2.x - pos1.x) / 2);
                    wps = [
                        { x: midX, y: pos1.y },
                        { x: midX, y: pos2.y }
                    ];
                }

                this.wires.push({
                    id: 'wire_' + Math.random().toString(36).substr(2, 6),
                    fromComp: this.connectingPin.compId, fromPin: this.connectingPin.pinId,
                    toComp: compId, toPin: pinId,
                    color: this.selectedWireColor, style: 'orthogonal',
                    waypoints: wps
                });
            }
            this.connectingPin = null;
            document.getElementById('tempWire').classList.add('hidden');
            this.renderComponents();
            this.renderWires();
            this.updateConnectionPanel();
        }
    },

    // Exact pin center position calculator
    getPinPos: function(compId, pinId) {
        const comp = this.components.find(c => c.id === compId);
        if (!comp) return null;
        const def = window.SimLabComponents[comp.type];
        if (!def) return null;
        const pin = def.pins.find(p => p.id === pinId);
        if (!pin) return null;

        const scale = comp.scale || 1.0;
        const rotation = comp.rotation || 0;
        const headerWidth = Math.max(145, def.width + 16);
        const offsetX = 8, offsetY = 36;
        const cx = headerWidth / 2, cy = offsetY + def.height / 2;
        const px = offsetX + pin.x - cx, py = offsetY + pin.y - cy;
        const rad = rotation * Math.PI / 180;
        const rx = px * Math.cos(rad) - py * Math.sin(rad);
        const ry = px * Math.sin(rad) + py * Math.cos(rad);
        return { x: comp.x + (cx + rx) * scale, y: comp.y + (cy + ry) * scale };
    },

    // Determine pin exit direction: 'V' (Vertical: top/bottom edge) or 'H' (Horizontal: left/right edge)
    getPinDir: function(compId, pinId) {
        const comp = this.components.find(c => c.id === compId);
        if (!comp) return 'V';
        const def = window.SimLabComponents[comp.type];
        if (!def) return 'V';
        const pin = def.pins ? def.pins.find(p => p.id === pinId) : null;
        if (!pin) return 'V';

        const w = def.width || 100;
        const h = def.height || 100;

        // Top or bottom edge -> exit Vertically (UP or DOWN)
        if (pin.y <= 35 || pin.y >= h - 35) {
            return 'V';
        }
        // Left or right edge -> exit Horizontally (LEFT or RIGHT)
        if (pin.x <= 35 || pin.x >= w - 35) {
            return 'H';
        }
        return 'V';
    },

    // ═══════════════════════════════════════════════════════════════
    // Wire Rendering — Smart Pin-Direction Orthogonal Wire Routing
    // Segmen yang terhubung ke PIN otomatis keluar Vertikal (V) atau Horizontal (H)
    // sesuai posisi pin pada komponen, dan memanjang/memendek secara presisi.
    // ═══════════════════════════════════════════════════════════════

    renderWires: function() {
        const group = document.getElementById('wiresGroup');
        group.innerHTML = '';
        const self = this;

        this.wires.forEach((wire, wireIdx) => {
            const pos1 = self.getPinPos(wire.fromComp, wire.fromPin);
            const pos2 = self.getPinPos(wire.toComp, wire.toPin);
            if (!pos1 || !pos2) return;

            const style = wire.style || self.wireStyleMode;
            const dir1 = self.getPinDir(wire.fromComp, wire.fromPin);
            const dir2 = self.getPinDir(wire.toComp, wire.toPin);

            // Dynamic channel offset calculation based on wire index & pin spacing
            // Creates 16px parallel channels to prevent lines from stacking on top of each other!
            const channelOffset = (wireIdx % 8 - 3.5) * 16;
            const pinStep = ((wireIdx * 10) % 40);

            let points = [];

            if (dir1 === 'V' && dir2 === 'V') {
                const dy1 = (pos2.y >= pos1.y ? (25 + pinStep) : (-25 - pinStep));
                const dy2 = (pos1.y >= pos2.y ? (25 + pinStep) : (-25 - pinStep));
                const y1 = self.snap(pos1.y + dy1);
                const y2 = self.snap(pos2.y + dy2);
                const xMid = self.snap((pos1.x + pos2.x) / 2 + channelOffset);

                const p1 = { x: pos1.x, y: y1 };
                const p2 = { x: xMid, y: y1 };
                const p3 = { x: xMid, y: y2 };
                const p4 = { x: pos2.x, y: y2 };

                points = [pos1, p1, p2, p3, p4, pos2];
            }
            else if (dir1 === 'V' && dir2 === 'H') {
                const dy1 = (pos2.y >= pos1.y ? (25 + pinStep) : (-25 - pinStep));
                const y1 = self.snap(pos1.y + dy1);
                const xMid = self.snap((pos1.x + pos2.x) / 2 + channelOffset);

                const p1 = { x: pos1.x, y: y1 };
                const p2 = { x: xMid, y: y1 };
                const p3 = { x: xMid, y: pos2.y };

                points = [pos1, p1, p2, p3, pos2];
            }
            else if (dir1 === 'H' && dir2 === 'V') {
                const dx1 = (pos2.x >= pos1.x ? (25 + pinStep) : (-25 - pinStep));
                const x1 = self.snap(pos1.x + dx1);
                const yMid = self.snap((pos1.y + pos2.y) / 2 + channelOffset);

                const p1 = { x: x1, y: pos1.y };
                const p2 = { x: x1, y: yMid };
                const p3 = { x: pos2.x, y: yMid };

                points = [pos1, p1, p2, p3, pos2];
            }
            else {
                // H-H
                const dx1 = (pos2.x >= pos1.x ? (25 + pinStep) : (-25 - pinStep));
                const dx2 = (pos1.x >= pos2.x ? (25 + pinStep) : (-25 - pinStep));
                const x1 = self.snap(pos1.x + dx1);
                const x2 = self.snap(pos2.x + dx2);
                const yMid = self.snap((pos1.y + pos2.y) / 2 + channelOffset);

                const p1 = { x: x1, y: pos1.y };
                const p2 = { x: x1, y: yMid };
                const p3 = { x: x2, y: yMid };
                const p4 = { x: x2, y: pos2.y };

                points = [pos1, p1, p2, p3, p4, pos2];
            }

            const isSelected = (self.selectedWireId === wire.id);

            // Build SVG pathD
            let pathD = `M ${points[0].x} ${points[0].y}`;
            for (let i = 1; i < points.length; i++) {
                pathD += ` L ${points[i].x} ${points[i].y}`;
            }

            // Selection Glow Effect (under path)
            if (isSelected) {
                const glow = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                glow.setAttribute('d', pathD);
                glow.setAttribute('stroke', '#0ea5e9');
                glow.setAttribute('stroke-width', '10');
                glow.setAttribute('fill', 'none');
                glow.setAttribute('opacity', '0.45');
                glow.setAttribute('stroke-linecap', 'round');
                glow.setAttribute('stroke-linejoin', 'round');
                group.appendChild(glow);
            }

            // Draw main wire path
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathD);
            path.setAttribute('stroke', wire.color || '#ef4444');
            path.setAttribute('stroke-width', isSelected ? '5' : '4');
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            if (style === 'dashed') path.setAttribute('stroke-dasharray', '8,6');
            path.style.cursor = 'pointer';
            path.style.pointerEvents = 'stroke';

            // Click selects wire
            path.addEventListener('click', function(e) {
                e.stopPropagation();
                self.selectedWireId = wire.id;
                self.renderWires();
            });

            path.addEventListener('contextmenu', function(e) {
                e.stopPropagation();
                self.selectedWireId = wire.id;
                self.showContextMenu(e, wire);
                self.renderWires();
            });
            group.appendChild(path);

            // Add Drag Handles ONLY if wire is selected! (Penanda H/V hanya muncul saat kabel diklik)
            if (isSelected && style !== 'curved') {
                for (let i = 1; i <= points.length - 3; i++) {
                    const pA = points[i];
                    const pB = points[i + 1];
                    const isV = (pA.x === pB.x);

                    const segIndex = i;
                    self._createSegmentDragHandle(group, wire, pA, pB, isV ? 'vertical' : 'horizontal', function(dx, dy) {
                        if (isV) {
                            // Vertical segment -> drag left/right (dx)
                            let newX;
                            if (segIndex === 1 && dir1 === 'H') {
                                newX = self.snap((b.dx1 !== undefined ? b.dx1 : (pos2.x >= pos1.x ? 30 : -30)) + dx);
                                if (Math.abs((pos1.x + newX) - pos2.x) < 8) newX = pos2.x - pos1.x; // Magnetic snap straight
                                b.dx1 = newX;
                            } else if (segIndex === points.length - 3 && dir2 === 'H') {
                                newX = self.snap((b.dx2 !== undefined ? b.dx2 : (pos1.x >= pos2.x ? 30 : -30)) + dx);
                                if (Math.abs((pos2.x + newX) - pos1.x) < 8) newX = pos1.x - pos2.x; // Magnetic snap straight
                                b.dx2 = newX;
                            } else {
                                let currX = (b.midX !== undefined ? b.midX : (pos1.x + pos2.x) / 2) + dx;
                                // Magnetic alignment snap to Pin1 or Pin2 X for 100% straight line
                                if (Math.abs(currX - pos1.x) < 8) currX = pos1.x;
                                else if (Math.abs(currX - pos2.x) < 8) currX = pos2.x;
                                b.midX = self.snap(currX);
                            }
                        } else {
                            // Horizontal segment -> drag up/down (dy)
                            let newY;
                            if (segIndex === 1 && dir1 === 'V') {
                                newY = self.snap((b.dy1 !== undefined ? b.dy1 : (pos2.y >= pos1.y ? 30 : -30)) + dy);
                                if (Math.abs((pos1.y + newY) - pos2.y) < 8) newY = pos2.y - pos1.y; // Magnetic snap straight
                                b.dy1 = newY;
                            } else if (segIndex === points.length - 3 && dir2 === 'V') {
                                newY = self.snap((b.dy2 !== undefined ? b.dy2 : (pos1.y >= pos2.y ? 30 : -30)) + dy);
                                if (Math.abs((pos2.y + newY) - pos1.y) < 8) newY = pos1.y - pos2.y; // Magnetic snap straight
                                b.dy2 = newY;
                            } else {
                                let currY = (b.midY !== undefined ? b.midY : (pos1.y + pos2.y) / 2) + dy;
                                // Magnetic alignment snap to Pin1 or Pin2 Y for 100% straight line
                                if (Math.abs(currY - pos1.y) < 8) currY = pos1.y;
                                else if (Math.abs(currY - pos2.y) < 8) currY = pos2.y;
                                b.midY = self.snap(currY);
                            }
                        }
                    });
                }
            }

            // Pin badges at wire endpoints
            const fromLabel = (self.components.find(c => c.id === wire.fromComp)?.label || 'Comp') + ':' + wire.fromPin;
            const toLabel = (self.components.find(c => c.id === wire.toComp)?.label || 'Comp') + ':' + wire.toPin;
            self.renderPinBadge(group, pos1.x, pos1.y, fromLabel, wire.color);
            self.renderPinBadge(group, pos2.x, pos2.y, toLabel, wire.color);
        });
    },

    // Create a drag handle on a segment
    _createSegmentDragHandle: function(group, wire, pA, pB, orientation, onDragFn) {
        const self = this;
        const mx = (pA.x + pB.x) / 2;
        const my = (pA.y + pB.y) / 2;
        const isV = orientation === 'vertical';

        const handle = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        if (isV) {
            const segLen = Math.max(16, Math.abs(pB.y - pA.y));
            handle.setAttribute('x', mx - 5);
            handle.setAttribute('y', my - Math.min(16, segLen / 2));
            handle.setAttribute('width', 10);
            handle.setAttribute('height', Math.min(32, segLen));
        } else {
            const segLen = Math.max(16, Math.abs(pB.x - pA.x));
            handle.setAttribute('x', mx - Math.min(16, segLen / 2));
            handle.setAttribute('y', my - 5);
            handle.setAttribute('width', Math.min(32, segLen));
            handle.setAttribute('height', 10);
        }
        handle.setAttribute('rx', 4);
        handle.setAttribute('fill', wire.color || '#ef4444');
        handle.setAttribute('stroke', '#fff');
        handle.setAttribute('stroke-width', '1.5');
        handle.style.cursor = isV ? 'ew-resize' : 'ns-resize';
        handle.style.pointerEvents = 'all';
        handle.style.opacity = '0.85';

        let startX, startY;
        handle.onmousedown = function(e) {
            e.preventDefault(); e.stopPropagation();
            startX = e.clientX; startY = e.clientY;
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onStop);
        };
        function onMove(e) {
            e.preventDefault();
            const dx = (e.clientX - startX) / self.zoomLevel;
            const dy = (e.clientY - startY) / self.zoomLevel;
            startX = e.clientX; startY = e.clientY;
            onDragFn(dx, dy);
            self.renderWires();
        }
        function onStop() {
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onStop);
        }

        handle.addEventListener('contextmenu', function(e) { self.showContextMenu(e, wire); });
        group.appendChild(handle);
    },

    renderPinBadge: function(group, x, y, text, color) {
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.style.pointerEvents = 'none';
        const w = Math.max(36, text.length * 5.5 + 10);
        const r = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        r.setAttribute('x', x - w / 2); r.setAttribute('y', y - 19);
        r.setAttribute('width', w); r.setAttribute('height', 14);
        r.setAttribute('rx', 4); r.setAttribute('fill', '#1e293b');
        r.setAttribute('stroke', color || '#10b981'); r.setAttribute('stroke-width', '1');
        r.setAttribute('opacity', '0.92');
        const t = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        t.setAttribute('x', x); t.setAttribute('y', y - 9);
        t.setAttribute('fill', '#fff'); t.setAttribute('font-size', '7.5');
        t.setAttribute('font-family', 'monospace'); t.setAttribute('font-weight', 'bold');
        t.setAttribute('text-anchor', 'middle');
        t.textContent = text;
        g.appendChild(r); g.appendChild(t); group.appendChild(g);
    },

    // ═══════════════════════════════════════════════════════════════
    // Connection Panel (Daftar Komponen & Koneksi)
    // ═══════════════════════════════════════════════════════════════

    updateConnectionPanel: function() {
        const tbody = document.getElementById('connPanelBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (this.wires.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-2 text-[10px]">Belum ada koneksi kabel</td></tr>';
            return;
        }

        this.wires.forEach((wire, idx) => {
            const fromComp = this.components.find(c => c.id === wire.fromComp);
            const toComp = this.components.find(c => c.id === wire.toComp);
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-700/50 hover:bg-gray-800/50 text-[10px]';
            tr.innerHTML = `
                <td class="px-2 py-1 text-gray-500">${idx + 1}</td>
                <td class="px-2 py-1 text-gray-200">${fromComp?.label || '?'}</td>
                <td class="px-2 py-1 text-emerald-400 font-mono">${wire.fromPin}</td>
                <td class="px-2 py-1 text-gray-200">${toComp?.label || '?'}</td>
                <td class="px-2 py-1 text-cyan-400 font-mono">${wire.toPin}</td>
            `;
            tbody.appendChild(tr);
        });

        // Update count badge in sidebar tab
        const badge = document.getElementById('connPanelCount');
        if (badge) badge.textContent = this.wires.length + ' kabel';
        // Also update tab button text with count
        const tabBtn = document.getElementById('tabBtnKoneksi');
        if (tabBtn) {
            const icon = '<i class="fas fa-project-diagram"></i> ';
            tabBtn.innerHTML = icon + 'Koneksi' + (this.wires.length > 0 ? ' (' + this.wires.length + ')' : '');
        }
    },

    // ═══════════════════════════════════════════════════════════════
    // Export / Import
    // ═══════════════════════════════════════════════════════════════

    exportJSON: function() {
        return {
            components: this.components.map(c => ({ id: c.id, type: c.type, x: c.x, y: c.y, scale: c.scale, rotation: c.rotation, label: c.label, state: c.state })),
            wires: this.wires,
            wireStyleMode: this.wireStyleMode,
            gridSize: this.gridSize,
            zoom: this.zoomLevel
        };
    },

    importJSON: function(data) {
        if (!data) return;
        this.components = data.components || [];
        this.wires = (data.wires || []).map(wire => {
            if (!wire.bend && wire.waypoints && wire.waypoints.length > 0) {
                const x1 = wire.waypoints[0].x || 100;
                const y = wire.waypoints[0].y || (wire.waypoints[1] ? wire.waypoints[1].y : 100);
                const x2 = (wire.waypoints[1] ? wire.waypoints[1].x : x1);
                wire.bend = { x1, y, x2 };
            }
            return wire;
        });
        if (data.wireStyleMode) this.wireStyleMode = data.wireStyleMode;
        if (data.gridSize) { this.gridSize = data.gridSize; this.drawGrid(); }
        this.renderAll();
        this.updateConnectionPanel();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.SimLabCircuit.init();
});
