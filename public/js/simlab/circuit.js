/**
 * PembdaHUB SimLab - Circuit Workspace Manager
 * Menangani rendering komponen visual, label nomor pin, drag & drop, penyambungan kabel 90° Lurus / Bezier, serta import/export JSON.
 */

window.SimLabCircuit = {
    components: [],
    wires: [],
    selectedWireColor: "#ef4444",
    connectingPin: null,
    compCounter: 0,
    zoomLevel: 1.0,
    wireStyleMode: "orthogonal", // "orthogonal" (lurus 90-derajat) atau "curved" (lengkung)

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
            self.zoomLevel = Math.min(2.0, self.zoomLevel + 0.15);
            self.applyZoom();
        });
        document.getElementById('btnZoomOut')?.addEventListener('click', function() {
            self.zoomLevel = Math.max(0.5, self.zoomLevel - 0.15);
            self.applyZoom();
        });

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
        document.getElementById('circuitCanvasContainer').addEventListener('click', function(e) {
            if (e.target.id === 'circuitCanvasContainer' || e.target.id === 'circuitSvg') {
                if (self.connectingPin) {
                    self.connectingPin = null;
                    document.getElementById('tempWire').classList.add('hidden');
                }
            }
        });
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

            const div = document.createElement('div');
            div.id = 'comp_' + comp.id;
            div.className = 'absolute pointer-events-auto bg-gray-900/50 rounded-xl p-2 border border-gray-700/60 shadow-2xl group hover:border-emerald-500/90 transition-all';
            div.style.left = comp.x + 'px';
            div.style.top = comp.y + 'px';
            div.style.width = (def.width + 16) + 'px';

            // Top Header Bar of Component
            let html = `
            <div class="flex items-center justify-between mb-1 handle cursor-move text-[10px] text-gray-400 border-b border-gray-800 pb-1">
                <span class="font-bold text-gray-200">${def.name}</span>
                <button onclick="SimLabCircuit.removeComponent('${comp.id}')" class="text-red-400 hover:text-red-300 font-bold px-1" title="Hapus Komponen">✕</button>
            </div>
            <div class="relative" style="width: ${def.width}px; height: ${def.height}px;">
                <svg width="${def.width}" height="${def.height}" viewBox="0 0 ${def.width} ${def.height}">
                    ${def.svg(comp)}
            `;

            // Render Pins + VISIBLE TEXT LABELS (Silkscreen Pin Numbers)
            def.pins.forEach(pin => {
                let textX = pin.x;
                let textY = pin.y;
                let textAnchor = "middle";

                // Smart positioning for Pin text labels
                if (pin.y <= 25) {
                    textY = pin.y + 16;
                } else if (pin.y >= def.height - 25) {
                    textY = pin.y - 9;
                } else if (pin.x <= 35) {
                    textX = pin.x + 12;
                    textY = pin.y + 3;
                    textAnchor = "start";
                } else if (pin.x >= def.width - 35) {
                    textX = pin.x - 12;
                    textY = pin.y + 3;
                    textAnchor = "end";
                }

                html += `
                <g class="pin-hover cursor-pointer" onclick="SimLabCircuit.onPinClick('${comp.id}', '${pin.id}')">
                    <circle cx="${pin.x}" cy="${pin.y}" r="6" fill="#10b981" stroke="#ffffff" stroke-width="1.5"/>
                    <circle cx="${pin.x}" cy="${pin.y}" r="2" fill="#000000"/>
                    <text x="${textX}" y="${textY}" fill="#ffffff" font-size="9.5" font-family="monospace" font-weight="900" text-anchor="${textAnchor}" pointer-events="none" style="text-shadow: 0 0 3px #000;">${pin.label}</text>
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

    makeDraggable: function(el, comp) {
        const self = this;
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        const handle = el.querySelector('.handle') || el;

        handle.onmousedown = dragMouseDown;

        function dragMouseDown(e) {
            e.preventDefault();
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
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
            document.onmouseup = null;
            document.onmousemove = null;
        }
    },

    onPinClick: function(compId, pinId) {
        if (!this.connectingPin) {
            // Start wire connection
            this.connectingPin = { compId: compId, pinId: pinId };
        } else {
            // Finish wire connection if target is different
            if (this.connectingPin.compId !== compId || this.connectingPin.pinId !== pinId) {
                const wire = {
                    id: 'wire_' + Math.random().toString(36).substr(2, 6),
                    fromComp: this.connectingPin.compId,
                    fromPin: this.connectingPin.pinId,
                    toComp: compId,
                    toPin: pinId,
                    color: this.selectedWireColor
                };
                this.wires.push(wire);
            }
            this.connectingPin = null;
            document.getElementById('tempWire').classList.add('hidden');
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

        // Account for component padding (8px) and header height (~24px)
        return {
            x: comp.x + 8 + pin.x,
            y: comp.y + 28 + pin.y
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

    renderWires: function() {
        const group = document.getElementById('wiresGroup');
        group.innerHTML = '';

        const self = this;

        this.wires.forEach(wire => {
            const pos1 = self.getPinPos(wire.fromComp, wire.fromPin);
            const pos2 = self.getPinPos(wire.toComp, wire.toPin);

            if (pos1 && pos2) {
                const d = self.calculateWirePath(pos1.x, pos1.y, pos2.x, pos2.y);
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', d);
                path.setAttribute('stroke', wire.color || '#ef4444');
                path.setAttribute('stroke-width', '4');
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                path.style.cursor = 'pointer';
                path.style.pointerEvents = 'stroke'; // Allow clicking wire overlaid on top of components!

                // Click to delete wire
                path.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (confirm('Hapus kabel ini?')) {
                        self.wires = self.wires.filter(w => w.id !== wire.id);
                        self.renderWires();
                    }
                });

                group.appendChild(path);
            }
        });
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
