/**
 * PembdaHUB SimLab - Arduino C++ Virtual Machine Interpreter Engine
 * VERSION: 3.0.0 (2026-07-29)
 *
 * Mesin Eksekusi VM Sekuensial Sejati:
 * - Kode C++ Arduino di-parse menjadi instruksi AST
 * - Instruksi dieksekusi satu per satu secara berurutan (sequential)
 * - delay(ms) menghentikan eksekusi VM selama ms milidetik REAL-TIME
 * - Status countdown delay ditampilkan di Serial Monitor
 */

window.SimLabEngine = {
    VERSION: '3.2.0',
    isRunning: false,
    simTimer: null,
    editor: null,
    pinStates: {},
    hasUnsavedChanges: false,
    autoCodeEnabled: true,
    currentProjectId: null,
    isGeneratingCode: false,

    // Virtual Machine State
    vm: {
        instructions: [],  // Parsed AST instructions
        pc: 0,             // Program Counter
        delayUntil: 0,     // Timestamp when current delay() expires
        loopCount: 0       // How many times loop() has completed
    },

    init: function() {
        console.log('[SimLab Engine] v' + this.VERSION + ' initialized');
        this.initEditor();
        this.bindEvents();
        this.loadInitialProject();
        this.initBeforeUnload();
        this.updateSaveBadge(false);
        this.updateAutoCodeBtn();
    },

    initEditor: function() {
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

            // Track code changes for unsaved badge & auto-code mode
            var self = this;
            this.editor.on('change', function() {
                if (!self.hasUnsavedChanges) {
                    self.hasUnsavedChanges = true;
                    self.updateSaveBadge(true);
                }
                if (!self.isGeneratingCode && self.autoCodeEnabled) {
                    self.autoCodeEnabled = false;
                    self.updateAutoCodeBtn();
                }
            });
        }
    },

    bindEvents: function() {
        const self = this;

        document.getElementById('btnRunSim')?.addEventListener('click', function() {
            if (self.isRunning) {
                self.stopSimulation();
            } else {
                self.startSimulation();
            }
        });

        document.getElementById('btnCompile')?.addEventListener('click', function() {
            self.compileCode();
        });

        document.getElementById('btnSaveProject')?.addEventListener('click', function() {
            self.saveProject();
        });

        // Serial Monitor Tab Controls
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

    // ═══════════════════════════════════════════════════════════════
    // C++ Arduino Code Parser → AST Instructions
    // ═══════════════════════════════════════════════════════════════

    extractFunctionBody: function(code, funcName) {
        // Find function signature
        const sigRegex = new RegExp('void\\s+' + funcName + '\\s*\\(\\s*\\)\\s*\\{');
        const sigMatch = sigRegex.exec(code);
        if (!sigMatch) return '';

        // Count braces to find matching closing brace
        let braceCount = 1;
        let i = sigMatch.index + sigMatch[0].length;
        const start = i;
        while (i < code.length && braceCount > 0) {
            if (code[i] === '{') braceCount++;
            if (code[i] === '}') braceCount--;
            i++;
        }
        return code.substring(start, i - 1);
    },

    parseBodyToInstructions: function(body) {
        const instructions = [];

        // Strip comments
        body = body.replace(/\/\/.*/g, '').replace(/\/\*[\s\S]*?\*\//g, '');

        // Split by semicolons
        const statements = body.split(';');

        for (let s = 0; s < statements.length; s++) {
            const line = statements[s].trim();
            if (!line) continue;

            // 1. digitalWrite(pin, val)
            const dwMatch = line.match(/digitalWrite\s*\(\s*([^,]+)\s*,\s*([^)]+)\s*\)/);
            if (dwMatch) {
                let pin = dwMatch[1].trim().replace(/['"]/g, '');
                if (pin === 'LED_BUILTIN') pin = '13';
                pin = pin.replace(/^D/, '');

                const valStr = dwMatch[2].trim();
                const val = (valStr === 'HIGH' || valStr === '1' || valStr === 'true') ? 1 : 0;
                instructions.push({ op: 'digitalWrite', pin: pin, val: val, src: line.trim() });
                continue;
            }

            // 2. delay(ms) — MUST parse the number correctly
            const delayMatch = line.match(/delay\s*\(\s*(\d+)\s*\)/);
            if (delayMatch) {
                const ms = parseInt(delayMatch[1], 10);
                instructions.push({ op: 'delay', ms: ms, src: line.trim() });
                continue;
            }

            // 3. Serial.println("...")
            const serialMatch = line.match(/Serial\.println\s*\(\s*"([^"]+)"\s*\)/);
            if (serialMatch) {
                instructions.push({ op: 'serialPrintln', msg: serialMatch[1], src: line.trim() });
                continue;
            }

            // 4. Serial.print("...")
            const serialPrintMatch = line.match(/Serial\.print\s*\(\s*"([^"]+)"\s*\)/);
            if (serialPrintMatch) {
                instructions.push({ op: 'serialPrint', msg: serialPrintMatch[1], src: line.trim() });
                continue;
            }

            // 5. servo.write(angle)
            const servoMatch = line.match(/(\w+)\.write\s*\(\s*(\d+)\s*\)/);
            if (servoMatch && servoMatch[1] !== 'Serial') {
                const angle = parseInt(servoMatch[2], 10) || 0;
                instructions.push({ op: 'servoWrite', angle: angle, src: line.trim() });
                continue;
            }
        }

        return instructions;
    },

    // ═══════════════════════════════════════════════════════════════
    // Simulation Start / Stop
    // ═══════════════════════════════════════════════════════════════

    startSimulation: function() {
        const code = this.getCode();
        if (!code.trim()) {
            alert('Tuliskan kode Arduino Anda terlebih dahulu.');
            return;
        }

        // Parse setup() and loop()
        const setupBody = this.extractFunctionBody(code, 'setup');
        const loopBody = this.extractFunctionBody(code, 'loop');

        const setupInstructions = this.parseBodyToInstructions(setupBody);
        const loopInstructions = this.parseBodyToInstructions(loopBody);

        if (loopInstructions.length === 0) {
            alert('Tidak ditemukan instruksi di dalam fungsi loop().');
            return;
        }

        // Initialize VM state
        this.vm.instructions = loopInstructions;
        this.vm.pc = 0;
        this.vm.delayUntil = 0;
        this.vm.loopCount = 0;
        this.isRunning = true;

        // UI Updates
        document.getElementById('simText').textContent = 'Hentikan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-square text-red-500';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-red-600/20';
        document.getElementById('simStatusBadge').classList.remove('hidden');
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
        document.getElementById('statusText').textContent = 'RUNNING (VM v' + this.VERSION + ')';

        // Serial Monitor: Show VM Debug Info
        this.appendSerialLog('\n══════════════════════════════════════\n');
        this.appendSerialLog('[VM v' + this.VERSION + '] SIMULATOR STARTED\n');
        this.appendSerialLog('[VM] Parsed ' + loopInstructions.length + ' loop() instructions:\n');
        for (let i = 0; i < loopInstructions.length; i++) {
            const instr = loopInstructions[i];
            let desc = '';
            if (instr.op === 'digitalWrite') desc = 'Pin ' + instr.pin + ' → ' + (instr.val ? 'HIGH' : 'LOW');
            else if (instr.op === 'delay') desc = 'TUNGGU ' + instr.ms + ' ms (' + (instr.ms / 1000).toFixed(1) + ' detik)';
            else if (instr.op === 'serialPrintln') desc = 'Serial: "' + instr.msg + '"';
            else desc = instr.src;
            this.appendSerialLog('  [' + i + '] ' + instr.op + ' → ' + desc + '\n');
        }
        this.appendSerialLog('══════════════════════════════════════\n');

        // Execute setup() instructions immediately (synchronous, no delays)
        for (let i = 0; i < setupInstructions.length; i++) {
            const instr = setupInstructions[i];
            if (instr.op === 'serialPrintln') {
                this.appendSerialLog(instr.msg + '\n');
            } else if (instr.op === 'serialPrint') {
                this.appendSerialLog(instr.msg);
            } else if (instr.op === 'digitalWrite') {
                this.setPinState(instr.pin, instr.val);
            }
        }

        // Start VM execution tick (every 20ms = 50Hz)
        const self = this;
        this.simTimer = setInterval(function() {
            self.vmTick();
        }, 20);
    },

    stopSimulation: function() {
        this.isRunning = false;
        if (this.simTimer) {
            clearInterval(this.simTimer);
            this.simTimer = null;
        }

        // Turn off all LEDs
        Object.keys(this.pinStates).forEach(pin => {
            this.setPinState(pin, 0);
        });
        this.pinStates = {};

        // UI Updates
        document.getElementById('simText').textContent = 'Jalankan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-play';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-emerald-500/20';
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-gray-500';
        document.getElementById('statusText').textContent = 'STOPPED';

        this.appendSerialLog('\n[VM] SIMULATOR STOPPED after ' + this.vm.loopCount + ' loop iterations\n');
    },

    // ═══════════════════════════════════════════════════════════════
    // VM Execution Tick — The Heart of the Simulator
    // ═══════════════════════════════════════════════════════════════

    vmTick: function() {
        if (!this.isRunning) return;

        const instructions = this.vm.instructions;
        if (!instructions || instructions.length === 0) return;

        const now = Date.now();

        // Are we currently waiting for a delay() to expire?
        if (this.vm.delayUntil > 0 && now < this.vm.delayUntil) {
            // Still waiting — update status text with countdown
            const remaining = Math.ceil((this.vm.delayUntil - now) / 1000);
            const statusEl = document.getElementById('statusText');
            if (statusEl) {
                statusEl.textContent = 'DELAY: ' + remaining + 's tersisa';
            }

            // Re-render active animated components (Motor DC rotor, buzzer, etc) continuously during delay
            const hasActiveAnim = window.SimLabCircuit && window.SimLabCircuit.components.some(c => c.state && (c.state.active || c.state.lit));
            if (hasActiveAnim) {
                window.SimLabCircuit.renderComponents();
            }
            return;
        }

        // Delay has expired — clear it and continue execution
        if (this.vm.delayUntil > 0) {
            this.vm.delayUntil = 0;
            document.getElementById('statusText').textContent = 'RUNNING (VM v' + this.VERSION + ')';
        }

        // Execute up to a few non-delay instructions per tick
        let safety = 0;
        while (safety < 50) {
            safety++;

            // Wrap around = one complete loop() iteration
            if (this.vm.pc >= instructions.length) {
                this.vm.pc = 0;
                this.vm.loopCount++;
            }

            const instr = instructions[this.vm.pc];
            this.vm.pc++;

            if (!instr) break;

            switch (instr.op) {
                case 'digitalWrite':
                    this.setPinState(instr.pin, instr.val);
                    break;

                case 'delay':
                    if (instr.ms > 0) {
                        // Set the absolute timestamp when this delay expires
                        this.vm.delayUntil = Date.now() + instr.ms;
                        // Immediately show delay info
                        const sec = (instr.ms / 1000).toFixed(1);
                        document.getElementById('statusText').textContent = 'DELAY: ' + sec + 's tersisa';
                        return; // EXIT tick — wait for delay to expire in future ticks
                    }
                    break;

                case 'serialPrintln':
                    this.appendSerialLog(instr.msg + '\n');
                    break;

                case 'serialPrint':
                    this.appendSerialLog(instr.msg);
                    break;

                case 'servoWrite':
                    this.updateAllServos(instr.angle);
                    break;
            }
        }
    },

    // ═══════════════════════════════════════════════════════════════
    // Pin State Manager & Wire/Resistor/LED Tracer
    // ═══════════════════════════════════════════════════════════════

    setPinState: function(pinNum, stateVal) {
        const cleanPin = pinNum.toString().replace(/^D/, '').trim();
        this.pinStates[cleanPin] = stateVal;

        // Update Arduino Uno onboard LED 13
        if (cleanPin === '13') {
            const unoComp = window.SimLabCircuit.components.find(c => c.type === 'uno');
            if (unoComp) {
                const led13 = document.getElementById('led_uno_13_' + unoComp.id);
                if (led13) {
                    led13.setAttribute('fill', stateVal === 1 ? '#ef4444' : '#451a03');
                }
            }
        }

        // Trace wires to find connected components
        const targetPinIds = ['D' + cleanPin, cleanPin, 'PIN_' + cleanPin];
        let needsReRender = false;

        window.SimLabCircuit.wires.forEach(wire => {
            let connectedCompId = null;
            let connectedPinId = null;

            if (targetPinIds.includes(wire.fromPin)) {
                connectedCompId = wire.toComp;
                connectedPinId = wire.toPin;
            } else if (targetPinIds.includes(wire.toPin)) {
                connectedCompId = wire.fromComp;
                connectedPinId = wire.fromPin;
            }

            if (!connectedCompId) return;

            const targetComp = window.SimLabCircuit.components.find(c => c.id === connectedCompId);
            if (!targetComp) return;

            // Direct LED connection
            if (targetComp.type.startsWith('led_')) {
                targetComp.state = targetComp.state || {};
                if (targetComp.state.lit !== (stateVal === 1)) {
                    targetComp.state.lit = (stateVal === 1);
                    needsReRender = true;
                }
            }
            // Motor DC
            else if (targetComp.type === 'motor_dc' || targetComp.type === 'l298n') {
                targetComp.state = targetComp.state || {};
                if (targetComp.state.active !== (stateVal === 1)) {
                    targetComp.state.active = (stateVal === 1);
                    needsReRender = true;
                }
            }
            // Resistor → trace through to LED or Motor on the other side
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
                        const nextComp = window.SimLabCircuit.components.find(c => c.id === nextCompId);
                        if (nextComp) {
                            nextComp.state = nextComp.state || {};
                            if (nextComp.type.startsWith('led_')) {
                                if (nextComp.state.lit !== (stateVal === 1)) {
                                    nextComp.state.lit = (stateVal === 1);
                                    needsReRender = true;
                                }
                            } else if (nextComp.type === 'motor_dc' || nextComp.type === 'relay' || nextComp.type === 'speaker') {
                                if (nextComp.state.active !== (stateVal === 1)) {
                                    nextComp.state.active = (stateVal === 1);
                                    needsReRender = true;
                                }
                            }
                        }
                    }
                });
            }
            // Relay, Speaker, DFPlayer, LCD
            else if (targetComp.type === 'relay' || targetComp.type === 'speaker' || targetComp.type === 'dfplayer' || targetComp.type.startsWith('lcd')) {
                targetComp.state = targetComp.state || {};
                if (targetComp.state.active !== (stateVal === 1)) {
                    targetComp.state.active = (stateVal === 1);
                    needsReRender = true;
                }
            }
            // Servo
            else if (targetComp.type === 'servo') {
                targetComp.state = targetComp.state || {};
                const newAngle = (stateVal === 1) ? 180 : 0;
                if (targetComp.state.angle !== newAngle) {
                    targetComp.state.angle = newAngle;
                    needsReRender = true;
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

    // ═══════════════════════════════════════════════════════════════
    // Sample Code Templates for Components
    // ═══════════════════════════════════════════════════════════════

    sampleTemplates: {
        led: `// PembdaHUB SimLab - Kode Kontrol LED
// Pin D13 terhubung ke Anoda LED

void setup() {
  pinMode(13, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Kontrol LED Dimulai!");
}

void loop() {
  digitalWrite(13, HIGH);
  Serial.println("LED Menyala (HIGH)...");
  delay(1000);

  digitalWrite(13, LOW);
  Serial.println("LED Padam (LOW)...");
  delay(1000);
}`,

        motor_dc: `// PembdaHUB SimLab - Kode Kontrol Motor DC
// Pin D3 terhubung ke Motor DC / Driver IN1

void setup() {
  pinMode(3, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Kontrol Motor DC Dimulai!");
}

void loop() {
  Serial.println("Motor DC BERPUTAR (HIGH)...");
  digitalWrite(3, HIGH);
  delay(3000);

  Serial.println("Motor DC BERHENTI (LOW)...");
  digitalWrite(3, LOW);
  delay(2000);
}`,

        servo: `// PembdaHUB SimLab - Kode Kontrol Servo SG90
// Pin D9 terhubung ke Pin PWM Servo

void setup() {
  pinMode(9, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Kontrol Servo SG90 Dimulai!");
}

void loop() {
  Serial.println("Servo Posisi 0 Derajat...");
  servo.write(0);
  digitalWrite(9, LOW);
  delay(1500);

  Serial.println("Servo Posisi 90 Derajat...");
  servo.write(90);
  digitalWrite(9, HIGH);
  delay(1500);

  Serial.println("Servo Posisi 180 Derajat...");
  servo.write(180);
  digitalWrite(9, HIGH);
  delay(1500);
}`,

        hc_sr04: `// PembdaHUB SimLab - Kode Sensor Jarak HC-SR04
// Trig = Pin 2, Echo = Pin 3

void setup() {
  pinMode(2, OUTPUT);
  pinMode(3, INPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Sensor HC-SR04 Siap!");
}

void loop() {
  digitalWrite(2, HIGH);
  delay(10);
  digitalWrite(2, LOW);
  Serial.println("Membaca Jarak Sensor: 45 cm");
  delay(1000);
}`,

        dht11: `// PembdaHUB SimLab - Kode Sensor Suhu & Kelembaban DHT11
// Data = Pin 4

void setup() {
  pinMode(4, INPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Sensor DHT11 Siap!");
}

void loop() {
  Serial.println("Membaca DHT11 -> Suhu: 28 C, Kelembaban: 65 %RH");
  delay(2000);
}`,

        lcd: `// PembdaHUB SimLab - Kode Layar LCD I2C
// SDA = Pin A4, SCL = Pin A5

void setup() {
  Serial.begin(9600);
  Serial.println("SimLab: Display LCD I2C Aktif!");
}

void loop() {
  Serial.println("LCD: PembdaHUB SimLab - Siap Digunakan!");
  delay(2000);
}`,

        relay: `// PembdaHUB SimLab - Kode Modul Relay 5V
// IN = Pin 7

void setup() {
  pinMode(7, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Modul Relay Dimulai!");
}

void loop() {
  Serial.println("Relay AKTIF (Switch NO Terhubung)...");
  digitalWrite(7, HIGH);
  delay(2500);

  Serial.println("Relay MATI (Switch NC Terhubung)...");
  digitalWrite(7, LOW);
  delay(2500);
}`,

        pir: `// PembdaHUB SimLab - Kode Sensor Gerak PIR
// OUT = Pin 2, LED Indikator = Pin 13

void setup() {
  pinMode(2, INPUT);
  pinMode(13, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Sensor Gerak PIR Aktif!");
}

void loop() {
  Serial.println("Mengecek Gerakan Manusia...");
  digitalWrite(13, HIGH);
  delay(1000);
  digitalWrite(13, LOW);
  delay(2000);
}`,

        speaker: `// PembdaHUB SimLab - Kode Speaker / Buzzer
// Signal = Pin 8

void setup() {
  pinMode(8, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: Speaker / Buzzer Dimulai!");
}

void loop() {
  Serial.println("Buzzer Bunyi (HIGH)...");
  digitalWrite(8, HIGH);
  delay(1000);

  Serial.println("Buzzer Diam (LOW)...");
  digitalWrite(8, LOW);
  delay(1500);
}`,

        ldr: `// PembdaHUB SimLab - Kode Sensor Cahaya LDR
// Pin Analog A0

void setup() {
  Serial.begin(9600);
  Serial.println("SimLab: Sensor Cahaya LDR Aktif!");
}

void loop() {
  Serial.println("Intensitas Cahaya LDR: 750 Lux (Terang)");
  delay(1500);
}`,

        rc522: `// PembdaHUB SimLab - Kode Reader RFID RC522
// SDA = Pin 10, RST = Pin 9

void setup() {
  pinMode(10, OUTPUT);
  pinMode(9, OUTPUT);
  Serial.begin(9600);
  Serial.println("SimLab: RFID RC522 Ready to Scan!");
}

void loop() {
  Serial.println("Menunggu Kartu Tap RFID...");
  delay(2000);
}`
    },

    loadSampleCode: function(templateKey) {
        if (!templateKey || !this.sampleTemplates[templateKey]) return;
        const code = this.sampleTemplates[templateKey];
        if (this.editor) {
            this.isGeneratingCode = true;
            this.editor.setValue(code);
            this.isGeneratingCode = false;
            this.showSaveNotification('Template Kode (' + templateKey.toUpperCase() + ') Berhasil Dimuat!', 'success');
        }
    },

    toggleAutoCode: function() {
        this.autoCodeEnabled = !this.autoCodeEnabled;
        this.updateAutoCodeBtn();
        if (this.autoCodeEnabled) {
            this.generateSmartCode();
            this.showSaveNotification('Auto-Code Pintar Diaktifkan!', 'success');
        } else {
            this.showSaveNotification('Auto-Code Dimatikan (Mode Manual)', 'info');
        }
    },

    updateAutoCodeBtn: function() {
        const btn = document.getElementById('btnToggleAutoCode');
        if (btn) {
            if (this.autoCodeEnabled) {
                btn.className = 'px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold hover:bg-emerald-500/30 transition-colors cursor-pointer';
                btn.innerHTML = '<i class="fas fa-magic text-amber-400 mr-1"></i> Auto-Code: ON';
            } else {
                btn.className = 'px-2 py-0.5 rounded bg-gray-800 text-gray-400 border border-gray-700 text-[10px] font-bold hover:bg-gray-700 transition-colors cursor-pointer';
                btn.innerHTML = '<i class="fas fa-magic mr-1"></i> Auto-Code: OFF';
            }
        }
    },

    generateSmartCode: function() {
        if (!this.autoCodeEnabled || !this.editor) return;

        const components = window.SimLabCircuit ? window.SimLabCircuit.components : [];
        const nonBoardComps = components.filter(c => c.type !== 'uno' && c.type !== 'nano' && c.type !== 'esp32');

        if (nonBoardComps.length === 0) {
            const baseCode = `// PembdaHUB SimLab - Kode Utama Arduino\n// Papan: Arduino Uno (ATmega328P)\n\nvoid setup() {\n  Serial.begin(9600);\n  Serial.println("PembdaHUB SimLab Berhasil Dimulai!");\n}\n\nvoid loop() {\n  // Tarik komponen ke kertas kerja untuk membuat kode otomatis\n  delay(1000);\n}`;
            this.isGeneratingCode = true;
            this.editor.setValue(baseCode);
            this.isGeneratingCode = false;
            return;
        }

        let setupLines = [];
        let loopBlocks = [];
        let compNames = [];
        const pinsUsed = new Set();

        nonBoardComps.forEach(comp => {
            const label = comp.label || comp.type;
            compNames.push(label);

            switch (comp.type) {
                case 'led_red':
                case 'led_green':
                case 'led_yellow':
                case 'led_white':
                    if (!pinsUsed.has(13)) {
                        pinsUsed.add(13);
                        setupLines.push(`  pinMode(13, OUTPUT); // Pin LED ${label}`);
                    }
                    loopBlocks.push(`  // --- LED (${label}) ---\n  digitalWrite(13, HIGH);\n  Serial.println("LED ${label}: Menyala (HIGH)");\n  delay(1000);\n  digitalWrite(13, LOW);\n  Serial.println("LED ${label}: Padam (LOW)");\n  delay(1000);`);
                    break;

                case 'motor_dc':
                case 'l298n':
                    if (!pinsUsed.has(3)) {
                        pinsUsed.add(3);
                        setupLines.push(`  pinMode(3, OUTPUT); // Pin Motor DC ${label}`);
                    }
                    loopBlocks.push(`  // --- Motor DC (${label}) ---\n  Serial.println("Motor DC ${label}: BERPUTAR...");\n  digitalWrite(3, HIGH);\n  delay(2500);\n  Serial.println("Motor DC ${label}: BERHENTI.");\n  digitalWrite(3, LOW);\n  delay(1500);`);
                    break;

                case 'servo':
                    if (!pinsUsed.has(9)) {
                        pinsUsed.add(9);
                        setupLines.push(`  pinMode(9, OUTPUT); // Pin Servo SG90 ${label}`);
                    }
                    loopBlocks.push(`  // --- Servo SG90 (${label}) ---\n  Serial.println("Servo ${label}: ke 0 Derajat");\n  servo.write(0);\n  delay(1000);\n  Serial.println("Servo ${label}: ke 90 Derajat");\n  servo.write(90);\n  delay(1000);\n  Serial.println("Servo ${label}: ke 180 Derajat");\n  servo.write(180);\n  delay(1000);`);
                    break;

                case 'hc_sr04':
                    if (!pinsUsed.has(2)) {
                        pinsUsed.add(2); pinsUsed.add(3);
                        setupLines.push(`  pinMode(2, OUTPUT); // Trig HC-SR04\n  pinMode(3, INPUT);  // Echo HC-SR04`);
                    }
                    loopBlocks.push(`  // --- Sensor Jarak HC-SR04 ---\n  digitalWrite(2, HIGH);\n  delay(10);\n  digitalWrite(2, LOW);\n  Serial.println("HC-SR04: Jarak Terbaca 45 cm");\n  delay(1000);`);
                    break;

                case 'dht11':
                    if (!pinsUsed.has(4)) {
                        pinsUsed.add(4);
                        setupLines.push(`  pinMode(4, INPUT); // Data DHT11`);
                    }
                    loopBlocks.push(`  // --- Sensor Suhu DHT11 ---\n  Serial.println("DHT11: Suhu 28 C, Kelembaban 65 %RH");\n  delay(1500);`);
                    break;

                case 'lcd1602':
                case 'lcd2004':
                    setupLines.push(`  // Inisialisasi Layar LCD I2C (${label})`);
                    loopBlocks.push(`  // --- Display LCD I2C (${label}) ---\n  Serial.println("LCD: PembdaHUB SimLab System Active");\n  delay(2000);`);
                    break;

                case 'relay':
                    if (!pinsUsed.has(7)) {
                        pinsUsed.add(7);
                        setupLines.push(`  pinMode(7, OUTPUT); // Control Pin Relay`);
                    }
                    loopBlocks.push(`  // --- Modul Relay 5V ---\n  Serial.println("Relay: Switch AKTIF (NO)");\n  digitalWrite(7, HIGH);\n  delay(2000);\n  Serial.println("Relay: Switch MATI (NC)");\n  digitalWrite(7, LOW);\n  delay(2000);`);
                    break;

                case 'pir':
                    if (!pinsUsed.has(2)) {
                        pinsUsed.add(2);
                        setupLines.push(`  pinMode(2, INPUT); // Signal OUT PIR`);
                    }
                    loopBlocks.push(`  // --- Sensor Gerak PIR ---\n  Serial.println("PIR: Membaca Deteksi Gerakan Manusia");\n  delay(1500);`);
                    break;

                case 'speaker':
                    if (!pinsUsed.has(8)) {
                        pinsUsed.add(8);
                        setupLines.push(`  pinMode(8, OUTPUT); // Signal Speaker/Buzzer`);
                    }
                    loopBlocks.push(`  // --- Speaker / Buzzer ---\n  Serial.println("Buzzer: BUNYI (HIGH)");\n  digitalWrite(8, HIGH);\n  delay(800);\n  digitalWrite(8, LOW);\n  delay(1000);`);
                    break;

                case 'ldr':
                    setupLines.push(`  // Pin Sensor Cahaya LDR (Analog A0)`);
                    loopBlocks.push(`  // --- Sensor Cahaya LDR ---\n  Serial.println("LDR: Intensitas Cahaya 750 Lux");\n  delay(1500);`);
                    break;

                case 'rc522':
                    setupLines.push(`  pinMode(10, OUTPUT); // SDA RFID\n  pinMode(9, OUTPUT);  // RST RFID`);
                    loopBlocks.push(`  // --- RFID RC522 Reader ---\n  Serial.println("RFID: Menunggu Tap Kartu...");\n  delay(2000);`);
                    break;
            }
        });

        let code = `// PembdaHUB SimLab - Kode Otomatis Tergenerasi\n`;
        code += `// Komponen Aktif (${nonBoardComps.length}): ${compNames.join(', ')}\n\n`;
        code += `void setup() {\n`;
        code += `  Serial.begin(9600);\n`;
        code += `  Serial.println("PembdaHUB SimLab System Ready!");\n`;
        if (setupLines.length > 0) {
            code += setupLines.join('\n') + `\n`;
        }
        code += `}\n\n`;
        code += `void loop() {\n`;
        code += loopBlocks.join('\n\n') + `\n`;
        code += `}`;

        this.isGeneratingCode = true;
        this.editor.setValue(code);
        this.isGeneratingCode = false;
    },

    newProject: function() {
        if (this.hasUnsavedChanges && !confirm('Buat proyek baru? Perubahan yang belum disimpan pada proyek ini akan hilang.')) {
            return;
        }

        window.SimLabConfig.projectId = null;
        this.currentProjectId = null;
        this.autoCodeEnabled = true;
        this.updateAutoCodeBtn();

        const titleEl = document.getElementById('projectTitle');
        if (titleEl) titleEl.value = 'Proyek SimLab Baru';

        if (window.SimLabCircuit) {
            window.SimLabCircuit.components = [];
            window.SimLabCircuit.wires = [];
            window.SimLabCircuit.addComponent('uno', 150, 100);
            window.SimLabCircuit.renderAll();
            window.SimLabCircuit.updateConnectionPanel();
        }

        this.generateSmartCode();
        this.hasUnsavedChanges = false;
        this.updateSaveBadge(false);
        this.showSaveNotification('Proyek Baru Berhasil Dibuat!', 'success');
    },

    openMyProjectsModal: function() {
        const modal = document.getElementById('modalMyProjects');
        if (!modal) return;
        modal.classList.remove('hidden');

        const container = document.getElementById('myProjectsListContainer');
        if (container) {
            container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-emerald-400 mb-2"></i>
                <p class="text-xs">Memuat daftar proyek Anda...</p>
            </div>`;
        }

        const self = this;
        fetch('/simlab/my-projects')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    self.renderMyProjectsModal(data.projects);
                } else {
                    if (container) container.innerHTML = '<p class="text-red-400 text-center py-4">Gagal memuat proyek.</p>';
                }
            })
            .catch(err => {
                if (container) container.innerHTML = '<p class="text-red-400 text-center py-4">Terjadi kesalahan koneksi.</p>';
            });
    },

    closeMyProjectsModal: function() {
        const modal = document.getElementById('modalMyProjects');
        if (modal) modal.classList.add('hidden');
    },

    renderMyProjectsModal: function(projects) {
        const container = document.getElementById('myProjectsListContainer');
        if (!container) return;

        if (!projects || projects.length === 0) {
            container.innerHTML = `
            <div class="text-center py-10 text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-3 opacity-40 text-emerald-400"></i>
                <p class="font-bold text-sm text-gray-300">Belum Ada Proyek Tersimpan</p>
                <p class="text-xs mt-1">Buat rangkaian & klik "Simpan Proyek" untuk membukanya kembali kapan saja.</p>
            </div>`;
            return;
        }

        let html = '';
        projects.forEach(p => {
            const boardName = p.board_type === 'uno' ? 'Arduino Uno' : (p.board_type === 'nano' ? 'Arduino Nano' : 'ESP32');
            const updatedDate = new Date(p.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

            html += `
            <div class="p-3.5 bg-gray-950 border border-gray-800 hover:border-emerald-500/50 rounded-xl flex items-center justify-between transition-all group">
                <div class="flex items-center space-x-3 truncate pr-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div class="truncate">
                        <h4 class="font-bold text-white text-sm truncate group-hover:text-emerald-400 transition-colors">${p.title}</h4>
                        <div class="flex items-center space-x-2 text-[11px] text-gray-400 mt-0.5">
                            <span class="bg-gray-800 px-2 py-0.5 rounded text-gray-300 font-medium">${boardName}</span>
                            <span>•</span>
                            <span>Diperbarui ${updatedDate}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2 shrink-0">
                    <button onclick="SimLabEngine.loadProjectFromModal('${p.id}')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center space-x-1 cursor-pointer">
                        <i class="fas fa-folder-open"></i>
                        <span>Muat Proyek</span>
                    </button>
                    <button onclick="SimLabEngine.deleteProjectFromModal('${p.id}', '${p.title.replace(/'/g, "\\'")}')" class="p-1.5 bg-red-950/60 hover:bg-red-600 text-red-400 hover:text-white rounded-xl border border-red-800/40 text-xs transition-colors cursor-pointer" title="Hapus Proyek">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;
        });

        container.innerHTML = html;
    },

    loadProjectFromModal: function(id) {
        const self = this;
        fetch('/simlab/project/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.project) {
                    const p = data.project;
                    window.SimLabConfig.projectId = p.id;
                    self.currentProjectId = p.id;
                    self.autoCodeEnabled = false;
                    self.updateAutoCodeBtn();

                    const titleEl = document.getElementById('projectTitle');
                    if (titleEl) titleEl.value = p.title;

                    const boardSelect = document.getElementById('boardTypeSelect');
                    if (boardSelect && p.board_type) boardSelect.value = p.board_type;

                    if (p.code_ino && self.editor) {
                        self.isGeneratingCode = true;
                        self.editor.setValue(p.code_ino);
                        self.isGeneratingCode = false;
                    }

                    if (p.circuit_json && window.SimLabCircuit) {
                        const circuit = typeof p.circuit_json === 'string' ? JSON.parse(p.circuit_json) : p.circuit_json;
                        window.SimLabCircuit.importJSON(circuit);
                    }

                    self.closeMyProjectsModal();
                    self.hasUnsavedChanges = false;
                    self.updateSaveBadge(false);
                    self.showSaveNotification('Proyek "' + p.title + '" Berhasil Dimuat!', 'success');
                }
            })
            .catch(err => {
                alert('Gagal memuat data proyek.');
            });
    },

    deleteProjectFromModal: function(id, title) {
        if (!confirm('Apakah Anda yakin ingin menghapus proyek "' + title + '"?')) return;

        const self = this;
        fetch('/simlab/project/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                self.showSaveNotification('Proyek Berhasil Dihapus!', 'success');
                self.openMyProjectsModal(); // Refresh modal list
            } else {
                alert(data.message || 'Gagal menghapus proyek.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan saat menghapus proyek.');
        });
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
            this.appendSerialLog('>> ' + input.value + '\n');
            input.value = '';
        }
    },

    saveProject: function() {
        const self = this;
        const title = document.getElementById('projectTitle')?.value || 'Proyek SimLab Tanpa Judul';
        const board = document.getElementById('boardTypeSelect')?.value || 'uno';
        const circuitJson = window.SimLabCircuit.exportJSON();
        const codeIno = this.getCode();

        const btnSave = document.getElementById('btnSaveProject');
        if (btnSave) {
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Menyimpan...</span>';
            btnSave.disabled = true;
        }

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
                if (res.project && res.project.id) {
                    window.SimLabConfig.projectId = res.project.id;
                }
                self.hasUnsavedChanges = false;
                self.updateSaveBadge(false);
                self.showSaveNotification('Proyek berhasil disimpan!', 'success');
            } else {
                self.showSaveNotification('Gagal: ' + res.message, 'error');
            }
        })
        .catch(err => {
            self.showSaveNotification('Error jaringan saat menyimpan.', 'error');
        })
        .finally(() => {
            if (btnSave) {
                btnSave.innerHTML = '<i class="fas fa-save"></i> <span>Simpan</span>';
                btnSave.disabled = false;
            }
        });
    },

    updateSaveBadge: function(unsaved) {
        let badge = document.getElementById('saveBadge');
        if (!badge) {
            badge = document.createElement('span');
            badge.id = 'saveBadge';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ml-2';
            const btnSave = document.getElementById('btnSaveProject');
            if (btnSave && btnSave.parentNode) {
                btnSave.parentNode.insertBefore(badge, btnSave.nextSibling);
            }
        }
        if (unsaved) {
            badge.textContent = '● Belum Tersimpan';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ml-2 bg-amber-500/20 text-amber-400 border border-amber-500/30';
        } else {
            badge.textContent = '✓ Tersimpan';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ml-2 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
        }
    },

    showSaveNotification: function(message, type) {
        let notif = document.getElementById('saveNotification');
        if (!notif) {
            notif = document.createElement('div');
            notif.id = 'saveNotification';
            notif.className = 'fixed top-20 right-4 z-50 px-4 py-2.5 rounded-xl shadow-2xl text-sm font-bold transition-all';
            document.body.appendChild(notif);
        }
        notif.textContent = message;
        notif.style.opacity = '1';
        if (type === 'success') {
            notif.className = 'fixed top-20 right-4 z-50 px-4 py-2.5 rounded-xl shadow-2xl text-sm font-bold bg-emerald-600 text-white';
        } else {
            notif.className = 'fixed top-20 right-4 z-50 px-4 py-2.5 rounded-xl shadow-2xl text-sm font-bold bg-red-600 text-white';
        }
        setTimeout(function() { notif.style.opacity = '0'; }, 2500);
        setTimeout(function() { notif.remove(); }, 3000);
    },

    initBeforeUnload: function() {
        var self = this;
        window.addEventListener('beforeunload', function(e) {
            if (self.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan yang belum tersimpan. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    window.SimLabEngine.init();
});

