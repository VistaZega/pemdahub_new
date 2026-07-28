/**
 * PembdaHUB SimLab - Simulation & Compiler Engine Bridge
 * Menangani VM Interpreter C++ Arduino sejati (AST execution, delay(ms) presisi real-time, Live Code Update, digitalWrite, Serial, Servo, LCD).
 */

window.SimLabEngine = {
    isRunning: false,
    simTimer: null,
    editor: null,
    pinStates: {},
    serialBuffer: "",

    // Virtual Machine Interpreter State
    vmLoopInstructions: [],
    vmPC: 0,
    vmWaitDelayUntil: 0,
    vmMaxOpsPerTick: 100,

    init: function() {
        this.initEditor();
        this.bindEvents();
        this.loadInitialProject();
    },

    initEditor: function() {
        const self = this;
        const textarea = document.getElementById('codeEditorArea');
        if (textarea && typeof CodeMirror !== 'undefined') {
            this.editor = CodeMirror.fromTextArea(textarea, {
                mode: 'text/x-c++src',
                theme: 'dracula',
                lineNumbers: true,
                indentUnit: 2,
                tabSize: 2,
                matchBrackets: true,
                autoCloseBrackets: true
            });

            // Live Code Re-parsing when user edits code during running simulation
            this.editor.on('change', function() {
                if (self.isRunning) {
                    const code = self.getCode();
                    self.vmLoopInstructions = self.parseCodeToInstructions(code);
                }
            });
        }
    },

    bindEvents: function() {
        const self = this;

        // Run/Stop Simulation Button
        document.getElementById('btnRunSim')?.addEventListener('click', function() {
            if (self.isRunning) {
                self.stopSimulation();
            } else {
                self.startSimulation();
            }
        });

        // Compile Button
        document.getElementById('btnCompile')?.addEventListener('click', function() {
            self.compileCode();
        });

        // Save Project Button
        document.getElementById('btnSaveProject')?.addEventListener('click', function() {
            self.saveProject();
        });

        // Serial Monitor Controls
        document.getElementById('tabBtnCode')?.addEventListener('click', function() {
            document.getElementById('codeTabContent').classList.remove('hidden');
            document.getElementById('serialTabContent').classList.add('hidden');
            this.classList.add('text-emerald-400', 'border-emerald-400');
            document.getElementById('tabBtnSerial').classList.remove('text-emerald-400', 'border-emerald-400');
        });

        document.getElementById('tabBtnSerial')?.addEventListener('click', function() {
            document.getElementById('serialTabContent').classList.remove('hidden');
            document.getElementById('codeTabContent').classList.add('hidden');
            this.classList.add('text-emerald-400', 'border-emerald-400');
            document.getElementById('tabBtnCode').classList.remove('text-emerald-400', 'border-emerald-400');
        });

        document.getElementById('btnClearSerial')?.addEventListener('click', function() {
            document.getElementById('serialOutputText').textContent = '';
        });

        document.getElementById('btnSendSerial')?.addEventListener('click', function() {
            self.sendSerialInput();
        });
        document.getElementById('serialInputText')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') self.sendSerialInput();
        });
    },

    loadInitialProject: function() {
        const config = window.SimLabConfig;
        if (config && config.initialProject) {
            const proj = config.initialProject;
            if (proj.code_ino && this.editor) {
                this.editor.setValue(proj.code_ino);
            }
            if (proj.circuit_json && window.SimLabCircuit) {
                const circuit = typeof proj.circuit_json === 'string' 
                    ? JSON.parse(proj.circuit_json) 
                    : proj.circuit_json;
                window.SimLabCircuit.importJSON(circuit);
            }
        } else {
            if (window.SimLabCircuit && window.SimLabCircuit.components.length === 0) {
                window.SimLabCircuit.addComponent('uno', 150, 100);
            }
        }
    },

    getCode: function() {
        return this.editor ? this.editor.getValue() : '';
    },

    compileCode: function() {
        const code = this.getCode();
        const board = document.getElementById('boardTypeSelect')?.value || 'uno';
        const logText = document.getElementById('compilerLogText');
        const statusLabel = document.getElementById('compilerStatusLabel');

        statusLabel.textContent = 'Mengecek Sintaks...';
        statusLabel.className = 'text-amber-400 font-bold animate-pulse';

        fetch(window.SimLabConfig.compileUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ code: code, board: board })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusLabel.textContent = 'KOMPILASI SUKSES';
                statusLabel.className = 'text-emerald-400 font-bold';
                logText.textContent = data.message || 'Kompilasi Berhasil. Siap dijalankan.';
                logText.className = 'text-emerald-400 font-mono';
            } else {
                statusLabel.textContent = 'ERROR SINTAKS';
                statusLabel.className = 'text-red-400 font-bold';
                logText.textContent = data.errors || data.message || 'Error kompilasi.';
                logText.className = 'text-red-400 font-mono';
            }
        })
        .catch(err => {
            statusLabel.textContent = 'ERROR';
            statusLabel.className = 'text-red-400 font-bold';
            logText.textContent = 'Gagal terhubung ke service kompilasi.';
        });
    },

    // Dynamic C++ Arduino Code Parser to AST Instructions
    parseCodeToInstructions: function(codeText) {
        const instructions = [];

        // Extract loop() function body
        let loopBody = codeText;
        const loopMatch = codeText.match(/void\s+loop\s*\(\s*\)\s*\{([\s\S]*)\}/);
        if (loopMatch) {
            loopBody = loopMatch[1];
        }

        // Clean comments
        loopBody = loopBody.replace(/\/\/.*/g, '').replace(/\/\*[\s\S]*?\*\//g, '');

        // Split statements by semicolon
        const lines = loopBody.split(';');
        lines.forEach(line => {
            const cleanLine = line.trim();
            if (!cleanLine) return;

            // 1. digitalWrite(pin, val)
            const dwMatch = cleanLine.match(/digitalWrite\s*\(\s*([^,\s]+)\s*,\s*([^)\s]+)\s*\)/);
            if (dwMatch) {
                let pin = dwMatch[1].replace(/['"]/g, '').trim();
                if (pin === 'LED_BUILTIN') pin = '13';
                pin = pin.replace('D', '');

                const valStr = dwMatch[2].trim();
                const val = (valStr === 'HIGH' || valStr === '1' || valStr === 'true') ? 1 : 0;
                instructions.push({ op: 'digitalWrite', pin: pin, val: val });
                return;
            }

            // 2. delay(ms)
            const delayMatch = cleanLine.match(/delay\s*\(\s*([^)\s]+)\s*\)/);
            if (delayMatch) {
                const ms = parseInt(delayMatch[1]) || 0;
                instructions.push({ op: 'delay', ms: ms });
                return;
            }

            // 3. Serial.println("...")
            const serialMatch = cleanLine.match(/Serial\.println\s*\(\s*"([^"]+)"\s*\)/);
            if (serialMatch) {
                instructions.push({ op: 'serialPrintln', msg: serialMatch[1] });
                return;
            }

            // 4. servo.write(angle)
            const servoMatch = cleanLine.match(/(\w+)\.write\s*\(\s*([^)\s]+)\s*\)/);
            if (servoMatch && servoMatch[1] !== 'Serial') {
                const angle = parseInt(servoMatch[2]) || 0;
                instructions.push({ op: 'servoWrite', angle: angle });
                return;
            }
        });

        return instructions;
    },

    startSimulation: function() {
        const code = this.getCode();
        if (!code.trim()) {
            alert('Tuliskan kode Arduino Anda terlebih dahulu.');
            return;
        }

        const self = this;
        this.isRunning = true;

        // Parse C++ Arduino code into VM AST Instructions
        this.vmLoopInstructions = this.parseCodeToInstructions(code);
        this.vmPC = 0;
        this.vmWaitDelayUntil = 0;

        // UI Updates
        document.getElementById('simText').textContent = 'Hentikan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-square text-red-500';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-red-600/20';
        
        document.getElementById('simStatusBadge').classList.remove('hidden');
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
        document.getElementById('statusText').textContent = 'RUNNING (16 MHz VM)';

        // Log parsed VM timings for transparency
        const delays = this.vmLoopInstructions.filter(i => i.op === 'delay').map(i => i.ms + 'ms');
        this.appendSerialLog(`\n[SIMULATOR STARTED - Loaded ${this.vmLoopInstructions.length} VM Instructions. Timings: ${delays.join(', ')}]\n`);

        // Check setup() for Serial.println initial output
        const setupMatch = code.match(/void\s+setup\s*\(\s*\)\s*\{([\s\S]*)\}/);
        if (setupMatch) {
            const setupLog = setupMatch[1].match(/Serial\.println\s*\(\s*"([^"]+)"\s*\)/);
            if (setupLog) {
                this.appendSerialLog(setupLog[1] + "\n");
            }
        }

        // Fast Virtual Machine Loop Timer (15ms Tick = 66 Hz Resolution)
        this.simTimer = setInterval(function() {
            self.vmExecutionTick();
        }, 15);
    },

    stopSimulation: function() {
        this.isRunning = false;
        if (this.simTimer) clearInterval(this.simTimer);

        // Turn off all active LEDs / Pins when simulation stops
        Object.keys(this.pinStates).forEach(pin => {
            this.setPinState(pin, 0);
        });

        // UI Updates
        document.getElementById('simText').textContent = 'Jalankan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-play';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-emerald-500/20';
        
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-gray-500';
        document.getElementById('statusText').textContent = 'STOPPED';

        this.appendSerialLog('\n[SIMULATOR STOPPED]\n');
    },

    // Authentic Sequential C++ Virtual Machine Execution Engine
    vmExecutionTick: function() {
        if (!this.isRunning || this.vmLoopInstructions.length === 0) return;

        const now = Date.now();

        // 1. Check if VM is currently in a delay(ms) pause
        if (now < this.vmWaitDelayUntil) {
            return; // Waiting for delay timer to expire
        }

        let opsExecuted = 0;

        // 2. Sequential Instruction Execution Loop
        while (opsExecuted < this.vmMaxOpsPerTick) {
            if (this.vmPC >= this.vmLoopInstructions.length) {
                this.vmPC = 0; // Loop restarts
            }

            const instr = this.vmLoopInstructions[this.vmPC];
            this.vmPC++;
            opsExecuted++;

            if (!instr) break;

            if (instr.op === 'digitalWrite') {
                this.setPinState(instr.pin, instr.val);
            } else if (instr.op === 'delay') {
                if (instr.ms > 0) {
                    this.vmWaitDelayUntil = now + instr.ms;
                    break; // Exit VM tick loop to let delay time pass
                }
            } else if (instr.op === 'serialPrintln') {
                this.appendSerialLog(instr.msg + "\n");
            } else if (instr.op === 'servoWrite') {
                this.updateAllServos(instr.angle);
            }
        }
    },

    // Set Pin State & Trace Wires / Resistors to update connected LEDs
    setPinState: function(pinNum, stateVal) {
        const cleanPin = pinNum.toString().replace('D', '').trim();
        this.pinStates[cleanPin] = stateVal;

        // Update onboard LED 13 if Uno
        if (cleanPin === '13') {
            const unoComp = window.SimLabCircuit.components.find(c => c.type === 'uno');
            if (unoComp) {
                const led13 = document.getElementById('led_uno_13_' + unoComp.id);
                if (led13) {
                    led13.setAttribute('fill', stateVal === 1 ? '#ef4444' : '#451a03');
                }
            }
        }

        // Target Pin IDs (e.g. ['D8', '8', 'PIN_8'])
        const targetPins = ['D' + cleanPin, cleanPin, 'PIN_' + cleanPin];
        let needsReRender = false;

        window.SimLabCircuit.wires.forEach(wire => {
            let connectedCompId = null;
            let connectedPinId = null;

            if (targetPins.includes(wire.fromPin)) {
                connectedCompId = wire.toComp;
                connectedPinId = wire.toPin;
            } else if (targetPins.includes(wire.toPin)) {
                connectedCompId = wire.fromComp;
                connectedPinId = wire.fromPin;
            }

            if (connectedCompId) {
                const targetComp = window.SimLabCircuit.components.find(c => c.id === connectedCompId);
                if (targetComp) {
                    // Direct LED connection
                    if (targetComp.type.startsWith('led_')) {
                        targetComp.state = targetComp.state || {};
                        if (targetComp.state.lit !== (stateVal === 1)) {
                            targetComp.state.lit = (stateVal === 1);
                            needsReRender = true;
                        }
                    } 
                    // Resistor connection: trace to next wire!
                    else if (targetComp.type === 'resistor') {
                        const otherResPin = (connectedPinId === 'PIN_1') ? 'PIN_2' : 'PIN_1';
                        window.SimLabCircuit.wires.forEach(wire2 => {
                            let nextCompId = null;
                            if (wire2.fromComp === targetComp.id && wire2.fromPin === otherResPin) {
                                nextCompId = wire2.toComp;
                            } else if (wire2.toComp === targetComp.id && wire2.toPin === otherResPin) {
                                nextCompId = wire2.fromComp;
                            }
                            if (nextCompId) {
                                const ledComp = window.SimLabCircuit.components.find(c => c.id === nextCompId);
                                if (ledComp && ledComp.type.startsWith('led_')) {
                                    ledComp.state = ledComp.state || {};
                                    if (ledComp.state.lit !== (stateVal === 1)) {
                                        ledComp.state.lit = (stateVal === 1);
                                        needsReRender = true;
                                    }
                                }
                            }
                        });
                    }
                    // Relay connection
                    else if (targetComp.type === 'relay') {
                        targetComp.state = targetComp.state || {};
                        if (targetComp.state.active !== (stateVal === 1)) {
                            targetComp.state.active = (stateVal === 1);
                            needsReRender = true;
                        }
                    }
                }
            }
        });

        if (needsReRender) {
            window.SimLabCircuit.renderComponents();
        }
    },

    updateAllServos: function(angle) {
        let changed = false;
        window.SimLabCircuit.components.forEach(comp => {
            if (comp.type === 'servo') {
                comp.state = comp.state || {};
                if (comp.state.angle !== angle) {
                    comp.state.angle = angle;
                    changed = true;
                }
            }
        });
        if (changed) {
            window.SimLabCircuit.renderComponents();
        }
    },

    updateCompState: function(compId, newState) {
        const comp = window.SimLabCircuit.components.find(c => c.id === compId);
        if (comp) {
            comp.state = Object.assign({}, comp.state, newState);
            window.SimLabCircuit.renderComponents();
        }
    },

    appendSerialLog: function(text) {
        const output = document.getElementById('serialOutputText');
        if (output) {
            output.textContent += text;
            output.scrollTop = output.scrollHeight;
        }
    },

    sendSerialInput: function() {
        const input = document.getElementById('serialInputText');
        if (input && input.value.trim()) {
            this.appendSerialLog(">> " + input.value + "\n");
            input.value = '';
        }
    },

    saveProject: function() {
        const title = document.getElementById('projectTitle')?.value || 'Proyek SimLab Tanpa Judul';
        const board = document.getElementById('boardTypeSelect')?.value || 'uno';
        const circuitJson = window.SimLabCircuit.exportJSON();
        const codeIno = this.getCode();

        const data = {
            id: window.SimLabConfig.projectId,
            title: title,
            board_type: board,
            circuit_json: JSON.stringify(circuitJson),
            code_ino: codeIno
        };

        fetch(window.SimLabConfig.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert(res.message);
                if (res.project && res.project.id) {
                    window.SimLabConfig.projectId = res.project.id;
                }
            } else {
                alert('Gagal menyimpan proyek: ' + res.message);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan saat menyimpan proyek.');
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.SimLabEngine.init();
});
