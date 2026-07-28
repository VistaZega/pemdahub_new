/**
 * PembdaHUB SimLab - Simulation & Compiler Engine Bridge
 * Menangani kompilasi C++, eksekusi simulasi real-time, sinkronisasi pin, dan Serial Monitor.
 */

window.SimLabEngine = {
    isRunning: false,
    simTimer: null,
    editor: null,
    pinStates: {},
    serialBuffer: "",

    init: function() {
        this.initEditor();
        this.bindEvents();
        this.loadInitialProject();
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
            // Load Default Board if Canvas is empty
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

    startSimulation: function() {
        const code = this.getCode();
        if (!code.trim()) {
            alert('Tuliskan kode Arduino Anda terlebih dahulu.');
            return;
        }

        const self = this;
        this.isRunning = true;

        // UI Updates
        document.getElementById('simText').textContent = 'Hentikan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-square text-red-500';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-red-600/20';
        
        document.getElementById('simStatusBadge').classList.remove('hidden');
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
        document.getElementById('statusText').textContent = 'RUNNING (16 MHz)';

        this.appendSerialLog('\n[SIMULATOR STARTED]\n');

        // Simulation Loop Execution Engine
        let stepCount = 0;
        this.simTimer = setInterval(function() {
            stepCount++;
            self.executionStep(stepCount);
        }, 100);
    },

    stopSimulation: function() {
        this.isRunning = false;
        if (this.simTimer) clearInterval(this.simTimer);

        // UI Updates
        document.getElementById('simText').textContent = 'Jalankan Simulasi';
        document.getElementById('simIcon').className = 'fas fa-play';
        document.getElementById('btnRunSim').className = 'px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-emerald-500/20';
        
        document.getElementById('statusIndicatorPin').className = 'w-2 h-2 rounded-full bg-gray-500';
        document.getElementById('statusText').textContent = 'STOPPED';

        this.appendSerialLog('\n[SIMULATOR STOPPED]\n');
    },

    executionStep: function(step) {
        const code = this.getCode();

        // Simulate LED Pin 13 Blink if code contains digitalWrite(13, ...) or digitalWrite(LED_BUILTIN, ...)
        if (code.includes('digitalWrite')) {
            const isHighStep = (step % 10) < 5;
            this.setPinState('13', isHighStep ? 1 : 0);
        }

        // Simulate Servo Angle sweep if code uses servo.write(...)
        if (code.includes('servo.write') || code.includes('Servo')) {
            const angle = (step * 15) % 180;
            this.updateAllServos(angle);
        }

        // Output Serial logs if Serial.println is present
        if (code.includes('Serial.println')) {
            if (step % 20 === 0) {
                // Check if reading distance sensor
                let msg = "PembdaHUB SimLab Tick #" + step;
                const ultraComp = window.SimLabCircuit.components.find(c => c.type === 'hc_sr04');
                if (ultraComp) {
                    const dist = ultraComp.state?.distance || 50;
                    msg = "Sensor HC-SR04 Jarak: " + dist + " cm";
                }
                this.appendSerialLog(msg + "\n");
            }
        }
    },

    setPinState: function(pinNum, stateVal) {
        this.pinStates[pinNum] = stateVal;

        // Update onboard LED 13 if Uno
        const unoComp = window.SimLabCircuit.components.find(c => c.type === 'uno');
        if (unoComp) {
            const led13 = document.getElementById('led_uno_13_' + unoComp.id);
            if (led13) {
                led13.setAttribute('fill', stateVal === 1 ? '#ef4444' : '#451a03');
            }
        }

        // Find connected external LEDs via wires connected to pin D13 or pinNum
        window.SimLabCircuit.wires.forEach(wire => {
            let targetCompId = null;
            if (wire.fromPin === 'D' + pinNum || wire.fromPin === pinNum) {
                targetCompId = wire.toComp;
            } else if (wire.toPin === 'D' + pinNum || wire.toPin === pinNum) {
                targetCompId = wire.fromComp;
            }

            if (targetCompId) {
                const targetComp = window.SimLabCircuit.components.find(c => c.id === targetCompId);
                if (targetComp && (targetComp.type.startsWith('led_'))) {
                    targetComp.state = targetComp.state || {};
                    targetComp.state.lit = (stateVal === 1);
                    window.SimLabCircuit.renderComponents();
                }
            }
        });
    },

    updateAllServos: function(angle) {
        window.SimLabCircuit.components.forEach(comp => {
            if (comp.type === 'servo') {
                comp.state = comp.state || {};
                comp.state.angle = angle;
            }
        });
        window.SimLabCircuit.renderComponents();
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
