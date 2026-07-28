/**
 * PembdaHUB SimLab - Circuit Workspace Manager
 * Menangani rendering komponen visual, label nomor pin resmi, rotasi (0°-360°), perbesar/perkecil (mouse wheel/tombol), drag & drop, penyambungan kabel 90° Wokwi (tarik sisi garis/waypoint), serta import/export JSON.
 */

window.SimLabCircuit = {
    components: [],
    wires: [],
    selectedWireColor: "#ef4444",
    connectingPin: null,
    compCounter: 0,
    zoomLevel: 1.0,
    wireStyleMode: "orthogonal",

    init: function() {
        this.bindEvents();
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

        // Click outside cancels wire drawing
        if (canvasContainer) {
            canvasContainer.addEventListener('click', function(e) {
                if (e.target.id === 'circuitCanvasContainer' || e.target.id === 'circuitSvg') {
                    if (self.connectingPin) {
                        self.connectingPin = null;
                        document.getElementById('tempWire').classList.add('hidden');
                        self.renderComponents();
                    }
                }
            });
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

            const div = document.createElement('div');
            div.id = 'comp_' + comp.id;
            div.className = 'absolute pointer-events-auto bg-gray-900/60 rounded-xl p-2 border border-gray-700/80 shadow-2xl group hover:border-emerald-500/90 transition-transform';
            div.style.left = comp.x + 'px';
            div.style.top = comp.y + 'px';
            div.style.width = (def.width + 16) + 'px';
            div.style.transform = `scale(${scale}) rotate(${rotation}deg)`;
            div.style.transformOrigin = 'center center';
            div.style.zIndex = '30';

            // Top Header Bar of Component with Rotate (⟳) & Scale (+/-) Controls
            let html = `
            <div class="flex items-center justify-between mb-1 handle cursor-move text-[10px] text-gray-400 border-b border-gray-800 pb-1 select-none">
                <span class="font-bold text-gray-200">${def.name}</span>
                <div class="flex items-center space-x-1">
                    <button onclick="SimLabCircuit.rotateComponent('${comp.id}')" class="text-cyan-400 hover:text-cyan-300 font-black px-1.5 py-0.5 text-xs hover:bg-gray-800 rounded" title="Rotasi Komponen (90°)">⟳</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', 0.15)" class="text-emerald-400 hover:text-emerald-300 font-black px-1.5 py-0.5 text-xs hover:bg-gray-800 rounded" title="Perbesar Komponen (+)">+</button>
                    <button onclick="SimLabCircuit.scaleComponent('${comp.id}', -0.15)" class="text-amber-400 hover:text-amber-300 font-black px-1.5 py-0.5 text-xs hover:bg-gray-800 rounded" title="Perkecil Komponen (-)">-</button>
                    <button onclick="SimLabCircuit.removeComponent('${comp.id}')" class="text-red-400 hover:text-red-300 font-black px-1.5 py-0.5 text-xs hover:bg-gray-800 rounded" title="Hapus Komponen">✕</button>
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
                const pos1 = this.getPinPos(this.connectingPin.compId, this.connectingPin.pinId);
                const pos2 = this.getPinPos(compId, pinId);

                let defaultWaypoints = [];
                if (pos1 && pos2) {
                    const midX = pos1.x + (pos2.x - pos1.x) / 2;
                    defaultWaypoints = [
                        { x: midX, y: pos1.y },
                        { x: midX, y: pos2.y }
                    ];
                }

                const wire = {
                    id: 'wire_' + Math.random().toString(36).substr(2, 6),
                    fromComp: this.connectingPin.compId,
                    fromPin: this.connectingPin.pinId,
                    toComp: compId,
                    toPin: pinId,
                    color: this.selectedWireColor,
                    waypoints: defaultWaypoints
                };
                this.wires.push(wire);
            }
            this.connectingPin = null;
            document.getElementById('tempWire').classList.add('hidden');
            this.renderComponents();
            this.renderWires();
        }
    },

    getPinPos: function(compId, pinId) {
        const comp = this.components.find(c => c.id === compId);
        if (!comp) return null;
        const def = window.SimLabComponents[comp.type];
        if (!def) return null;
        const pin = def.pins.find(p => p.id === pinId);
        if (!pin) return null;

        const scale = comp.scale || 1.0;
        const rotation = comp.rotation || 0;

        // Center of component box
        const cx = (def.width + 16) / 2;
        const cy = (def.height + 28) / 2;

        // Pin pos relative to center
        const px = (8 + pin.x) - cx;
        const py = (28 + pin.y) - cy;

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

            if (dx > dy) {
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

    // Render Wires + Wokwi Interactive Waypoint Drag Handles
    renderWires: function() {
        const group = document.getElementById('wiresGroup');
        group.innerHTML = '';

        const self = this;

        this.wires.forEach(wire => {
            const pos1 = self.getPinPos(wire.fromComp, wire.fromPin);
            const pos2 = self.getPinPos(wire.toComp, wire.toPin);

            if (!pos1 || !pos2) return;

            // Auto initialize default 90-deg waypoints if empty
            if (!wire.waypoints || wire.waypoints.length === 0) {
                const midX = pos1.x + (pos2.x - pos1.x) / 2;
                wire.waypoints = [
                    { x: midX, y: pos1.y },
                    { x: midX, y: pos2.y }
                ];
            }

            // Build Path String
            let pathD = `M ${pos1.x} ${pos1.y}`;
            wire.waypoints.forEach(wp => {
                pathD += ` L ${wp.x} ${wp.y}`;
            });
            pathD += ` L ${pos2.x} ${pos2.y}`;

            // Render Wire Path
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathD);
            path.setAttribute('stroke', wire.color || '#ef4444');
            path.setAttribute('stroke-width', '5');
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.style.cursor = 'pointer';
            path.style.pointerEvents = 'stroke';

            // Double-click wire to add a new bend waypoint at click position
            path.addEventListener('dblclick', function(e) {
                e.stopPropagation();
                const rect = document.getElementById('circuitSvg').getBoundingClientRect();
                const clickX = (e.clientX - rect.left) / self.zoomLevel;
                const clickY = (e.clientY - rect.top) / self.zoomLevel;
                wire.waypoints.push({ x: clickX, y: clickY });
                self.renderWires();
            });

            // Shift+Click or Click to delete wire
            path.addEventListener('click', function(e) {
                e.stopPropagation();
                if (confirm('Hapus kabel sambungan ini?')) {
                    self.wires = self.wires.filter(w => w.id !== wire.id);
                    self.renderWires();
                }
            });

            group.appendChild(path);

            // Render Wokwi Interactive Waypoint Drag Handles (Lingkaran penarik sisi garis kabel)
            wire.waypoints.forEach((wp, idx) => {
                const handle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                handle.setAttribute('cx', wp.x);
                handle.setAttribute('cy', wp.y);
                handle.setAttribute('r', 5);
                handle.setAttribute('fill', wire.color || '#ef4444');
                handle.setAttribute('stroke', '#ffffff');
                handle.setAttribute('stroke-width', '2');
                handle.style.cursor = 'grab';
                handle.style.pointerEvents = 'all';

                // Drag Waypoint Handle (Tarik Sisi Garis Kabel Wokwi-style)
                self.makeWaypointDraggable(handle, wire, idx);

                // Double-click handle to remove this bend waypoint
                handle.addEventListener('dblclick', function(e) {
                    e.stopPropagation();
                    if (wire.waypoints.length > 1) {
                        wire.waypoints.splice(idx, 1);
                        self.renderWires();
                    }
                });

                group.appendChild(handle);
            });
        });
    },

    makeWaypointDraggable: function(el, wire, wpIndex) {
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

            wire.waypoints[wpIndex].x += dx;
            wire.waypoints[wpIndex].y += dy;

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
