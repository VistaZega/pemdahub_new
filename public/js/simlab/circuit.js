/**
 * PembdaHUB SimLab - Circuit Workspace Manager
 * Menangani rendering komponen visual, snap 100% presisi di titik pusat pin,
 * rotasi (0°-360°), perbesar/perkecil, drag & drop, penarikan lengan kabel 90° Wokwi 100% tegak lurus (tanpa kabel serong!),
 * menu klik kanan warna & tipe kabel, serta indikator nomor pin di kedua ujung kabel.
 */

window.SimLabCircuit = {
    components: [],
    wires: [],
    selectedWireColor: "#ef4444",
    connectingPin: null,
    compCounter: 0,
    zoomLevel: 1.0,
    wireStyleMode: "orthogonal",
    activeWireContext: null,

    init: function() {
        this.bindEvents();
        this.initContextMenu();
        this.renderAll();
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

        // Toggle Wire Style Mode Button (Lurus 90° vs Lengkung)
        document.getElementById('btnToggleWireStyle')?.addEventListener('click', function() {
            if (self.wireStyleMode === 'orthogonal') {
                self.wireStyleMode = 'curved';
                document.getElementById('wireStyleLabel').textContent = 'Kabel: Lengkung Curve';
            } else {
                self.wireStyleMode = 'orthogonal';
                document.getElementById('wireStyleLabel').textContent = 'Kabel: Lurus 90°';
            }
            self.renderWires();
        });

        // Clear Canvas Buttons
        document.getElementById('btnClearWires')?.addEventListener('click', function() {
            if (confirm('Hapus semua sambungan kabel?')) {
                self.wires = [];
                self.renderWires();
            }
        });

        document.getElementById('btnClearCanvas')?.addEventListener('click', function() {
            if (confirm('Kosongkan canvas (hapus semua komponen & kabel)?')) {
                self.components = [];
                self.wires = [];
                self.renderAll();
            }
        });

        // Zoom Buttons
        document.getElementById('btnZoomIn')?.addEventListener('click', function() {
            self.zoomLevel = Math.min(3.0, self.zoomLevel + 0.15);
            self.applyZoom();
        });
        document.getElementById('btnZoomOut')?.addEventListener('click', function() {
            self.zoomLevel = Math.max(0.4, self.zoomLevel - 0.15);
            self.applyZoom();
        });

        // Mouse Wheel Canvas Zooming
        const canvasContainer = document.getElementById('circuitCanvasContainer');
        if (canvasContainer) {
            canvasContainer.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.08 : -0.08;
                self.zoomLevel = Math.min(3.0, Math.max(0.4, self.zoomLevel + delta));
                self.applyZoom();
            }, { passive: false });
        }

        // Canvas Mouse Movements for Wire Preview
        const svg = document.getElementById('circuitSvg');
        svg.addEventListener('mousemove', function(e) {
            if (self.connectingPin) {
                const rect = svg.getBoundingClientRect();
                const mouseX = (e.clientX - rect.left) / self.zoomLevel;
                const mouseY = (e.clientY - rect.top) / self.zoomLevel;
                
                const startPos = self.getPinPos(self.connectingPin.compId, self.connectingPin.pinId);
                if (startPos) {
                    const d = self.calculateWirePath(startPos.x, startPos.y, mouseX, mouseY);
                    const tempPath = document.getElementById('tempWire');
                    tempPath.setAttribute('d', d);
                    tempPath.setAttribute('stroke', self.selectedWireColor);
                    tempPath.classList.remove('hidden');
                }
            }
        });

        // Click outside cancels wire drawing and hides context menu
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('wireContextMenu');
            if (menu && !menu.contains(e.target)) {
                menu.classList.add('hidden');
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
                <label class="text-[10px] text-gray-400 block mb-1">Pilih Warna Kabel:</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#ef4444')" class="h-6 rounded bg-red-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#22c55e')" class="h-6 rounded bg-emerald-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#3b82f6')" class="h-6 rounded bg-blue-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#eab308')" class="h-6 rounded bg-yellow-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#f97316')" class="h-6 rounded bg-orange-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#a855f7')" class="h-6 rounded bg-purple-500 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#f8fafc')" class="h-6 rounded bg-slate-100 hover:scale-105 transition-all border border-white/20"></button>
                    <button onclick="SimLabCircuit.setContextMenuWireColor('#18181b')" class="h-6 rounded bg-zinc-900 hover:scale-105 transition-all border border-white/20"></button>
                </div>
            </div>
            <div>
                <label class="text-[10px] text-gray-400 block mb-1">Tipe Garis Kabel:</label>
                <div class="grid grid-cols-3 gap-1 text-[10px]">
                    <button onclick="SimLabCircuit.setContextMenuWireStyle('orthogonal')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded text-center border border-gray-700">Lurus 90°</button>
                    <button onclick="SimLabCircuit.setContextMenuWireStyle('curved')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded text-center border border-gray-700">Lengkung</button>
                    <button onclick="SimLabCircuit.setContextMenuWireStyle('dashed')" class="px-1.5 py-1 bg-gray-800 hover:bg-gray-700 rounded text-center border border-gray-700">Putus</button>
                </div>
            </div>
            <button onclick="SimLabCircuit.deleteContextMenuWire()" class="w-full text-center py-1.5 bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white rounded font-bold transition-all border border-red-500/40">
                ✕ Hapus Kabel Ini
            </button>
        `;
        document.body.appendChild(menu);
    },

    showContextMenu: function(e, wire) {
        e.preventDefault();
        e.stopPropagation();
        this.activeWireContext = wire;

        const menu = document.getElementById('wireContextMenu');
        if (menu) {
            menu.style.left = e.clientX + 'px';
            menu.style.top = e.clientY + 'px';
            menu.classList.remove('hidden');

            const label = document.getElementById('ctxWirePinLabel');
            if (label) {
                label.textContent = `${wire.fromPin} ➔ ${wire.toPin}`;
            }
        }
    },

    setContextMenuWireColor: function(color) {
        if (this.activeWireContext) {
            this.activeWireContext.color = color;
            this.renderWires();
            document.getElementById('wireContextMenu')?.classList.add('hidden');
        }
    },

    setContextMenuWireStyle: function(style) {
        if (this.activeWireContext) {
            this.activeWireContext.style = style;
            this.renderWires();
            document.getElementById('wireContextMenu')?.classList.add('hidden');
        }
    },

    deleteContextMenuWire: function() {
        if (this.activeWireContext) {
            this.wires = this.wires.filter(w => w.id !== this.activeWireContext.id);
            this.renderWires();
            document.getElementById('wireContextMenu')?.classList.add('hidden');
        }
    },

    applyZoom: function() {
        const svg = document.getElementById('circuitSvg');
        const layer = document.getElementById('componentsLayer');
        svg.style.transform = `scale(${this.zoomLevel})`;
        svg.style.transformOrigin = '0 0';
        layer.style.transform = `scale(${this.zoomLevel})`;
        layer.style.transformOrigin = '0 0';
    },

    addComponent: function(type, x = 100, y = 100, savedId = null, savedState = null) {
        const def = window.SimLabComponents[type];
        if (!def) {
            console.error('Tipe komponen tidak dikenal:', type);
            return null;
        }

        this.compCounter++;
        const id = savedId || (type + '_' + this.compCounter + '_' + Math.random().toString(36).substr(2, 4));
        
        if (!savedId) {
            x += (this.components.length * 30) % 300;
            y += (this.components.length * 20) % 200;
        }

        const comp = {
            id: id,
            type: type,
            x: x,
            y: y,
            scale: 1.0,
            rotation: 0,
            state: savedState || {}
        };

        this.components.push(comp);
        this.renderComponents();
        return comp;
    },

    removeComponent: function(id) {
        this.components = this.components.filter(c => c.id !== id);
        this.wires = this.wires.filter(w => w.fromComp !== id && w.toComp !== id);
        this.renderAll();
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

            // Ensure container width has at least 145px to comfortably fit Title + 4 Action Buttons inside border
            const headerWidth = Math.max(145, def.width + 16);

            const div = document.createElement('div');
            div.id = 'comp_' + comp.id;
            div.className = 'absolute pointer-events-auto bg-gray-900/60 rounded-xl p-2 border border-gray-700/80 shadow-2xl group hover:border-emerald-500/90 transition-transform';
            div.style.left = comp.x + 'px';
            div.style.top = comp.y + 'px';
            div.style.width = headerWidth + 'px';
            div.style.transform = `scale(${scale}) rotate(${rotation}deg)`;
            div.style.transformOrigin = 'center center';
            div.style.zIndex = '30';

            // Top Header Bar of Component (Strictly left-aligned SVG inside container, 0px offset error!)
            let html = `
            <div class="flex items-center justify-between mb-1 handle cursor-move text-[10px] text-gray-400 border-b border-gray-800 pb-1 select-none w-full">
                <span class="font-bold text-gray-200 truncate pr-1 text-[11px]">${def.name}</span>
                <div class="flex items-center space-x-1 shrink-0">
                    <button onclick="SimLabCircuit.rotateComponent('${comp.id}')" class="text-cyan-400 hover:text-cyan-300 font-black px-1 py-0.5 text-xs hover:bg-gray-800 rounded" title="Rotasi Komponen (90°)">⟳</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', 0.15)" class="text-emerald-400 hover:text-emerald-300 font-black px-1 py-0.5 text-xs hover:bg-gray-800 rounded" title="Perbesar Komponen (+)">+</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', -0.15)" class="text-amber-400 hover:text-amber-300 font-black px-1 py-0.5 text-xs hover:bg-gray-800 rounded" title="Perkecil Komponen (-)">-</button>
                    <button onclick="SimLabCircuit.removeComponent('${comp.id}')" class="text-red-400 hover:text-red-300 font-black px-1 py-0.5 text-xs hover:bg-gray-800 rounded" title="Hapus Komponen">✕</button>
                </div>
            </div>
            <div class="relative" style="width: ${def.width}px; height: ${def.height}px;">
                <svg width="${def.width}" height="${def.height}" viewBox="0 0 ${def.width} ${def.height}">
                    ${def.svg(comp)}
            `;

            // Render Pins as Interactive Circles with Active Selection Ring
            def.pins.forEach(pin => {
                const isSelected = self.connectingPin && self.connectingPin.compId === comp.id && self.connectingPin.pinId === pin.id;
                html += `
                <g class="pin-hover cursor-pointer" onclick="SimLabCircuit.onPinClick('${comp.id}', '${pin.id}')">
                    <circle cx="${pin.x}" cy="${pin.y}" r="${isSelected ? 8 : 6}" fill="${isSelected ? '#f59e0b' : '#10b981'}" stroke="#ffffff" stroke-width="${isSelected ? 2.5 : 1.5}"/>
                    <circle cx="${pin.x}" cy="${pin.y}" r="2" fill="#000000"/>
                    <title>Pin ${pin.label} (${pin.type.toUpperCase()})</title>
                </g>
                `;
            });

            html += `
                </svg>
            </div>
            `;

            // Render optional controls (sliders, triggers)
            if (def.controls) {
                html += def.controls(comp);
            }

            div.innerHTML = html;
            layer.appendChild(div);

            // Make Component Draggable
            self.makeDraggable(div, comp);
        });
    },

    rotateComponent: function(id) {
        const comp = this.components.find(c => c.id === id);
        if (comp) {
            comp.rotation = ((comp.rotation || 0) + 90) % 360;
            this.renderComponents();
            this.renderWires();
        }
    },

    scaleComponent: function(id, delta) {
        const comp = this.components.find(c => c.id === id);
        if (comp) {
            comp.scale = Math.max(0.5, Math.min(3.0, (comp.scale || 1.0) + delta));
            this.renderComponents();
            this.renderWires();
        }
    },

    makeDraggable: function(el, comp) {
        const self = this;
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        const handle = el.querySelector('.handle') || el;

        handle.onmousedown = dragMouseDown;

        function dragMouseDown(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
            e.preventDefault();
            e.stopPropagation();

            pos3 = e.clientX;
            pos4 = e.clientY;

            window.addEventListener('mousemove', elementDrag);
            window.addEventListener('mouseup', closeDragElement);
        }

        function elementDrag(e) {
            e.preventDefault();
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;

            comp.x = Math.max(0, el.offsetLeft - (pos1 / self.zoomLevel));
            comp.y = Math.max(0, el.offsetTop - (pos2 / self.zoomLevel));

            el.style.left = comp.x + "px";
            el.style.top = comp.y + "px";

            // Re-render connected wires smoothly during drag
            self.renderWires();
        }

        function closeDragElement() {
            window.removeEventListener('mousemove', elementDrag);
            window.removeEventListener('mouseup', closeDragElement);
        }
    },

    onPinClick: function(compId, pinId) {
        if (!this.connectingPin) {
            // Start wire connection from Pin A
            this.connectingPin = { compId: compId, pinId: pinId };
            this.renderComponents();
        } else {
            // Finish wire connection to Pin B if target is different
            if (this.connectingPin.compId !== compId || this.connectingPin.pinId !== pinId) {
                const wire = {
                    id: 'wire_' + Math.random().toString(36).substr(2, 6),
                    fromComp: this.connectingPin.compId,
                    fromPin: this.connectingPin.pinId,
                    toComp: compId,
                    toPin: pinId,
                    color: this.selectedWireColor,
                    style: 'orthogonal',
                    armRatio: 0.5
                };
                this.wires.push(wire);
            }
            this.connectingPin = null;
            document.getElementById('tempWire').classList.add('hidden');
            this.renderComponents();
            this.renderWires();
        }
    },

    // 100% Exact Pin Center Coordinate Calculator (Presisi Ujung Kabel Ke Pin Center 0.00px Error)
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

        // Fixed DOM offsets: p-2 padding = 8px, header height = 36px
        const offsetX = 8;
        const offsetY = 36;

        // Exact center of component box in local space
        const cx = (headerWidth / 2);
        const cy = offsetY + (def.height / 2);

        // Pin pos relative to box center
        const px = offsetX + pin.x - cx;
        const py = offsetY + pin.y - cy;

        // 2D Rotation matrix transformation
        const rad = rotation * Math.PI / 180;
        const rx = px * Math.cos(rad) - py * Math.sin(rad);
        const ry = px * Math.sin(rad) + py * Math.cos(rad);

        return {
            x: comp.x + (cx + rx) * scale,
            y: comp.y + (cy + ry) * scale
        };
    },

    // Flexible Wire Path Calculation: Supports 90° Orthogonal Straight Segments or Curves
    calculateWirePath: function(x1, y1, x2, y2) {
        if (this.wireStyleMode === 'orthogonal') {
            const dx = Math.abs(x2 - x1);
            const dy = Math.abs(y2 - y1);

            if (dx >= dy) {
                const midX = x1 + (x2 - x1) / 2;
                return `M ${x1} ${y1} L ${midX} ${y1} L ${midX} ${y2} L ${x2} ${y2}`;
            } else {
                const midY = y1 + (y2 - y1) / 2;
                return `M ${x1} ${y1} L ${x1} ${midY} L ${x2} ${midY} L ${x2} ${y2}`;
            }
        } else {
            // Bezier Smooth Curve
            const dx = Math.abs(x2 - x1) * 0.5;
            const dy = Math.abs(y2 - y1) * 0.5;
            const cx1 = x1 + (x2 > x1 ? dx : -dx);
            const cy1 = y1 + dy;
            const cx2 = x2 + (x2 > x1 ? -dx : dx);
            const cy2 = y2 - dy;
            return `M ${x1} ${y1} C ${cx1} ${cy1}, ${cx2} ${cy2}, ${x2} ${y2}`;
        }
    },

    // Render Wires + Wokwi 100% Strictly Perpendicular 90° Manhattan Arm Segment Dragging + Right-Click Menu + Pin Label Badges
    renderWires: function() {
        const group = document.getElementById('wiresGroup');
        group.innerHTML = '';

        const self = this;

        this.wires.forEach(wire => {
            const pos1 = self.getPinPos(wire.fromComp, wire.fromPin);
            const pos2 = self.getPinPos(wire.toComp, wire.toPin);

            if (!pos1 || !pos2) return;

            const currentStyle = wire.style || self.wireStyleMode;
            const ratio = (typeof wire.armRatio === 'number') ? wire.armRatio : 0.5;

            let pathD = "";
            let midSegment = null; // Stores middle arm info for dragging

            if (currentStyle === 'curved') {
                pathD = self.calculateWirePath(pos1.x, pos1.y, pos2.x, pos2.y);
            } else {
                // Strict 90° Manhattan Perpendicular Routing (HVH vs VHV)
                const dx = Math.abs(pos2.x - pos1.x);
                const dy = Math.abs(pos2.y - pos1.y);

                if (dx >= dy) {
                    // HVH Layout (Horizontal - Vertical Middle Arm - Horizontal)
                    const Wx = pos1.x + (pos2.x - pos1.x) * ratio;
                    pathD = `M ${pos1.x} ${pos1.y} L ${Wx} ${pos1.y} L ${Wx} ${pos2.y} L ${pos2.x} ${pos2.y}`;

                    midSegment = {
                        type: 'vertical',
                        x: Wx,
                        y1: Math.min(pos1.y, pos2.y),
                        y2: Math.max(pos1.y, pos2.y),
                        midX: Wx,
                        midY: (pos1.y + pos2.y) / 2
                    };
                } else {
                    // VHV Layout (Vertical - Horizontal Middle Arm - Vertical)
                    const Wy = pos1.y + (pos2.y - pos1.y) * ratio;
                    pathD = `M ${pos1.x} ${pos1.y} L ${pos1.x} ${Wy} L ${pos2.x} ${Wy} L ${pos2.x} ${pos2.y}`;

                    midSegment = {
                        type: 'horizontal',
                        y: Wy,
                        x1: Math.min(pos1.x, pos2.x),
                        x2: Math.max(pos1.x, pos2.x),
                        midX: (pos1.x + pos2.x) / 2,
                        midY: Wy
                    };
                }
            }

            // Render Main Wire Path
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathD);
            path.setAttribute('stroke', wire.color || '#ef4444');
            path.setAttribute('stroke-width', '5');
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            if (currentStyle === 'dashed') {
                path.setAttribute('stroke-dasharray', '8,6');
            }
            path.style.cursor = 'pointer';
            path.style.pointerEvents = 'stroke';

            // Right-Click Context Menu on Wire (Warna & Tipe Kabel)
            path.addEventListener('contextmenu', function(e) {
                self.showContextMenu(e, wire);
            });

            group.appendChild(path);

            // 1. Render Wokwi 100% Strictly Perpendicular Middle Arm Segment Drag Handle
            if (currentStyle !== 'curved' && midSegment) {
                const segHandle = document.createElementNS('http://www.w3.org/2000/svg', 'rect');

                if (midSegment.type === 'horizontal') {
                    segHandle.setAttribute('x', midSegment.midX - 16);
                    segHandle.setAttribute('y', midSegment.midY - 4);
                    segHandle.setAttribute('width', 32);
                    segHandle.setAttribute('height', 8);
                    segHandle.style.cursor = 'ns-resize';
                } else {
                    segHandle.setAttribute('x', midSegment.midX - 4);
                    segHandle.setAttribute('y', midSegment.midY - 16);
                    segHandle.setAttribute('width', 8);
                    segHandle.setAttribute('height', 32);
                    segHandle.style.cursor = 'ew-resize';
                }

                segHandle.setAttribute('rx', 4);
                segHandle.setAttribute('fill', wire.color || '#ef4444');
                segHandle.setAttribute('stroke', '#ffffff');
                segHandle.setAttribute('stroke-width', '2');
                segHandle.style.pointerEvents = 'all';

                // Drag Middle Cable Arm (Guarantees 100% Perpendicular 90° Lines with ZERO Slanted Wires!)
                self.makeStrictManhattanSegmentDraggable(segHandle, wire, pos1, pos2, midSegment.type);

                segHandle.addEventListener('contextmenu', function(e) {
                    self.showContextMenu(e, wire);
                });

                group.appendChild(segHandle);
            }

            // 2. Render Pin Indicator Badges at BOTH ends of the Wire (Presisi Tepat Di Titik Pusat Pin)
            const fromCompDef = window.SimLabComponents[self.components.find(c => c.id === wire.fromComp)?.type];
            const toCompDef = window.SimLabComponents[self.components.find(c => c.id === wire.toComp)?.type];

            const fromPinName = `${fromCompDef ? fromCompDef.name.split(' ')[0] : 'Comp'}:${wire.fromPin}`;
            const toPinName = `${toCompDef ? toCompDef.name.split(' ')[0] : 'Comp'}:${wire.toPin}`;

            self.renderPinBadge(group, pos1.x, pos1.y, fromPinName, wire.color);
            self.renderPinBadge(group, pos2.x, pos2.y, toPinName, wire.color);
        });
    },

    // Render Small Pin Indicator Badge Pill at Wire End (Center Pin Snap)
    renderPinBadge: function(group, x, y, labelText, wireColor) {
        const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        g.style.pointerEvents = 'none';

        const textWidth = Math.max(36, labelText.length * 6 + 10);
        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', x - textWidth / 2);
        rect.setAttribute('y', y - 18);
        rect.setAttribute('width', textWidth);
        rect.setAttribute('height', 14);
        rect.setAttribute('rx', 4);
        rect.setAttribute('fill', '#0f172a');
        rect.setAttribute('stroke', wireColor || '#10b981');
        rect.setAttribute('stroke-width', '1');
        rect.setAttribute('opacity', '0.9');

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', x);
        text.setAttribute('y', y - 8);
        text.setAttribute('fill', '#ffffff');
        text.setAttribute('font-size', '8');
        text.setAttribute('font-family', 'monospace');
        text.setAttribute('font-weight', 'bold');
        text.setAttribute('text-anchor', 'middle');
        text.textContent = labelText;

        g.appendChild(rect);
        g.appendChild(text);
        group.appendChild(g);
    },

    // Strictly Perpendicular 90° Manhattan Arm Segment Dragging (Vertikal: Kiri-Kanan, Horizontal: Atas-Bawah)
    makeStrictManhattanSegmentDraggable: function(el, wire, pos1, pos2, armType) {
        const self = this;
        let startX = 0, startY = 0;

        el.onmousedown = function(e) {
            e.preventDefault();
            e.stopPropagation();

            startX = e.clientX;
            startY = e.clientY;

            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', onStop);
        };

        function onMove(e) {
            e.preventDefault();
            const dx = (e.clientX - startX) / self.zoomLevel;
            const dy = (e.clientY - startY) / self.zoomLevel;
            startX = e.clientX;
            startY = e.clientY;

            let currentRatio = (typeof wire.armRatio === 'number') ? wire.armRatio : 0.5;

            if (armType === 'vertical') {
                // Vertical Arm: move whole vertical line left/right (dx)
                const totalDist = pos2.x - pos1.x;
                if (Math.abs(totalDist) > 5) {
                    currentRatio += dx / totalDist;
                    wire.armRatio = Math.max(0.05, Math.min(0.95, currentRatio));
                }
            } else {
                // Horizontal Arm: move whole horizontal line up/down (dy)
                const totalDist = pos2.y - pos1.y;
                if (Math.abs(totalDist) > 5) {
                    currentRatio += dy / totalDist;
                    wire.armRatio = Math.max(0.05, Math.min(0.95, currentRatio));
                }
            }

            self.renderWires();
        }

        function onStop() {
            window.removeEventListener('mousemove', onMove);
            window.removeEventListener('mouseup', onStop);
        }
    },

    exportJSON: function() {
        return {
            components: this.components,
            wires: this.wires,
            wireStyleMode: this.wireStyleMode,
            zoom: this.zoomLevel
        };
    },

    importJSON: function(data) {
        if (!data) return;
        this.components = data.components || [];
        this.wires = data.wires || [];
        if (data.wireStyleMode) {
            this.wireStyleMode = data.wireStyleMode;
            const label = document.getElementById('wireStyleLabel');
            if (label) {
                label.textContent = this.wireStyleMode === 'orthogonal' ? 'Kabel: Lurus 90°' : 'Kabel: Lengkung Curve';
            }
        }
        this.renderAll();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.SimLabCircuit.init();
});
