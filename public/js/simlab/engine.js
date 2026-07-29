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
    VERSION: '3.1.0',
    isRunning: false,
    simTimer: null,
    editor: null,
    pinStates: {},
    hasUnsavedChanges: false,

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

            // Track code changes for unsaved badge
            var self = this;
            this.editor.on('change', function() {
                if (!self.hasUnsavedChanges) {
                    self.hasUnsavedChanges = true;
                    self.updateSaveBadge(true);
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
            this.editor.setValue(code);
            this.showSaveNotification('Template Kode (' + templateKey.toUpperCase() + ') Berhasil Dimuat!', 'success');
        }
    }

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

