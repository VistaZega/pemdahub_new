/**
 * PembdaHUB SimLab - Component Definitions Library
 * Mendefinisikan SVG visual, letak Pin, dan tipe interaktivitas untuk 16+ Komponen Elektronika & Board.
 */

window.SimLabComponents = {
    // ----------------------------------------------------
    // 1. ARDUINO UNO (ATmega328P) - Enhanced Spacious Design
    // ----------------------------------------------------
    uno: {
        name: "Arduino Uno",
        width: 320,
        height: 200,
        bg: "#008784",
        pins: [
            // Top Digital Pins (D0 - D13 + GND + AREF + SDA + SCL)
            { id: "SCL", label: "SCL", x: 295, y: 18, type: "digital" },
            { id: "SDA", label: "SDA", x: 280, y: 18, type: "digital" },
            { id: "AREF", label: "AREF", x: 265, y: 18, type: "analog" },
            { id: "GND_TOP", label: "GND", x: 250, y: 18, type: "gnd" },
            { id: "D13", label: "13", x: 235, y: 18, type: "digital", pwm: true },
            { id: "D12", label: "12", x: 220, y: 18, type: "digital" },
            { id: "D11", label: "~11", x: 205, y: 18, type: "digital", pwm: true },
            { id: "D10", label: "~10", x: 190, y: 18, type: "digital", pwm: true },
            { id: "D9", label: "~9", x: 175, y: 18, type: "digital", pwm: true },
            { id: "D8", label: "8", x: 160, y: 18, type: "digital" },
            { id: "D7", label: "7", x: 130, y: 18, type: "digital" },
            { id: "D6", label: "~6", x: 115, y: 18, type: "digital", pwm: true },
            { id: "D5", label: "~5", x: 100, y: 18, type: "digital", pwm: true },
            { id: "D4", label: "4", x: 85, y: 18, type: "digital" },
            { id: "D3", label: "~3", x: 70, y: 18, type: "digital", pwm: true },
            { id: "D2", label: "2", x: 55, y: 18, type: "digital" },
            { id: "TX", label: "1 (TX)", x: 40, y: 18, type: "serial" },
            { id: "RX", label: "0 (RX)", x: 25, y: 18, type: "serial" },

            // Bottom Power & Analog Pins
            { id: "RESET", label: "RST", x: 60, y: 182, type: "power" },
            { id: "3V3", label: "3.3V", x: 75, y: 182, type: "power" },
            { id: "5V", label: "5V", x: 90, y: 182, type: "power" },
            { id: "GND_1", label: "GND", x: 105, y: 182, type: "gnd" },
            { id: "GND_2", label: "GND", x: 120, y: 182, type: "gnd" },
            { id: "VIN", label: "VIN", x: 135, y: 182, type: "power" },
            
            { id: "A0", label: "A0", x: 175, y: 182, type: "analog" },
            { id: "A1", label: "A1", x: 190, y: 182, type: "analog" },
            { id: "A2", label: "A2", x: 205, y: 182, type: "analog" },
            { id: "A3", label: "A3", x: 220, y: 182, type: "analog" },
            { id: "A4", label: "A4", x: 235, y: 182, type: "analog" },
            { id: "A5", label: "A5", x: 250, y: 182, type: "analog" }
        ],
        svg: function(comp) {
            return `
            <rect width="320" height="200" rx="12" fill="#008784" stroke="#005d5b" stroke-width="3"/>
            <rect x="20" y="80" width="140" height="32" rx="4" fill="#1e293b"/>
            <text x="90" y="100" fill="#94a3b8" font-size="11" font-family="monospace" text-anchor="middle" font-weight="bold">ATMEGA328P-PU</text>
            <rect x="15" y="130" width="45" height="50" rx="4" fill="#475569"/>
            <text x="37" y="160" fill="#e2e8f0" font-size="9" text-anchor="middle">USB B</text>
            <circle cx="285" cy="155" r="16" fill="#0f172a"/>
            <text x="285" y="159" fill="#94a3b8" font-size="8" text-anchor="middle">POWER</text>
            <text x="175" y="130" fill="#ffffff" font-size="16" font-family="sans-serif" font-weight="900" text-anchor="middle">ARDUINO UNO</text>
            
            <circle id="led_uno_13_${comp.id}" cx="245" cy="55" r="4" fill="#451a03" stroke="#78350f" stroke-width="1"/>
            <text x="255" y="58" fill="#cbd5e1" font-size="8">L (D13)</text>
            <circle id="led_uno_on_${comp.id}" cx="245" cy="70" r="4" fill="#15803d" stroke="#22c55e" stroke-width="1"/>
            <text x="255" y="73" fill="#cbd5e1" font-size="8">ON</text>

            <!-- Top Header Silkscreen Labels (Rapi, Rotated -90° text-anchor="start" (Mulai y=30 ke bawah, 0 Overlap!)) -->
            <g fill="#ffffff" font-size="9" font-family="monospace" font-weight="bold" text-anchor="start">
                <text x="295" y="30" transform="rotate(-90 295 30)">SCL</text>
                <text x="280" y="30" transform="rotate(-90 280 30)">SDA</text>
                <text x="265" y="30" transform="rotate(-90 265 30)">AREF</text>
                <text x="250" y="30" transform="rotate(-90 250 30)">GND</text>
                <text x="235" y="30" transform="rotate(-90 235 30)">13</text>
                <text x="220" y="30" transform="rotate(-90 220 30)">12</text>
                <text x="205" y="30" transform="rotate(-90 205 30)">~11</text>
                <text x="190" y="30" transform="rotate(-90 190 30)">~10</text>
                <text x="175" y="30" transform="rotate(-90 175 30)">~9</text>
                <text x="160" y="30" transform="rotate(-90 160 30)">8</text>
                <text x="130" y="30" transform="rotate(-90 130 30)">7</text>
                <text x="115" y="30" transform="rotate(-90 115 30)">~6</text>
                <text x="100" y="30" transform="rotate(-90 100 30)">~5</text>
                <text x="85" y="30" transform="rotate(-90 85 30)">4</text>
                <text x="70" y="30" transform="rotate(-90 70 30)">~3</text>
                <text x="55" y="30" transform="rotate(-90 55 30)">2</text>
                <text x="40" y="30" transform="rotate(-90 40 30)">TX</text>
                <text x="25" y="30" transform="rotate(-90 25 30)">RX</text>
            </g>

            <!-- Bottom Power/Analog Silkscreen Pin Labels (Rotated -90° text-anchor="end" (Selesai y=170 ke atas)) -->
            <g fill="#ffffff" font-size="9" font-family="monospace" font-weight="bold" text-anchor="end">
                <text x="60" y="170" transform="rotate(-90 60 170)">RST</text>
                <text x="75" y="170" transform="rotate(-90 75 170)">3.3V</text>
                <text x="90" y="170" transform="rotate(-90 90 170)">5V</text>
                <text x="105" y="170" transform="rotate(-90 105 170)">GND</text>
                <text x="120" y="170" transform="rotate(-90 120 170)">GND</text>
                <text x="135" y="170" transform="rotate(-90 135 170)">VIN</text>
                <text x="175" y="170" transform="rotate(-90 175 170)">A0</text>
                <text x="190" y="170" transform="rotate(-90 190 170)">A1</text>
                <text x="205" y="170" transform="rotate(-90 205 170)">A2</text>
                <text x="220" y="170" transform="rotate(-90 220 170)">A3</text>
                <text x="235" y="170" transform="rotate(-90 235 170)">A4</text>
                <text x="250" y="170" transform="rotate(-90 250 170)">A5</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 2. ARDUINO NANO
    // ----------------------------------------------------
    nano: {
        name: "Arduino Nano",
        width: 150,
        height: 240,
        bg: "#0284c7",
        pins: [
            { id: "D13", label: "D13", x: 18, y: 35, type: "digital" },
            { id: "3V3", label: "3V3", x: 18, y: 50, type: "power" },
            { id: "REF", label: "REF", x: 18, y: 65, type: "analog" },
            { id: "A0", label: "A0", x: 18, y: 80, type: "analog" },
            { id: "A1", label: "A1", x: 18, y: 95, type: "analog" },
            { id: "A2", label: "A2", x: 18, y: 110, type: "analog" },
            { id: "A3", label: "A3", x: 18, y: 125, type: "analog" },
            { id: "A4", label: "A4", x: 18, y: 140, type: "analog" },
            { id: "A5", label: "A5", x: 18, y: 155, type: "analog" },
            { id: "A6", label: "A6", x: 18, y: 170, type: "analog" },
            { id: "A7", label: "A7", x: 18, y: 185, type: "analog" },
            { id: "5V", label: "5V", x: 18, y: 200, type: "power" },

            { id: "D12", label: "D12", x: 132, y: 35, type: "digital" },
            { id: "D11", label: "D11", x: 132, y: 50, type: "digital" },
            { id: "D10", label: "D10", x: 132, y: 65, type: "digital" },
            { id: "D9", label: "D9", x: 132, y: 80, type: "digital" },
            { id: "D8", label: "D8", x: 132, y: 95, type: "digital" },
            { id: "D7", label: "D7", x: 132, y: 110, type: "digital" },
            { id: "D6", label: "D6", x: 132, y: 125, type: "digital" },
            { id: "D5", label: "D5", x: 132, y: 140, type: "digital" },
            { id: "D4", label: "D4", x: 132, y: 155, type: "digital" },
            { id: "D3", label: "D3", x: 132, y: 170, type: "digital" },
            { id: "D2", label: "D2", x: 132, y: 185, type: "digital" },
            { id: "GND", label: "GND", x: 132, y: 200, type: "gnd" }
        ],
        svg: function(comp) {
            return `
            <rect width="150" height="240" rx="8" fill="#0284c7" stroke="#0369a1" stroke-width="3"/>
            <rect x="45" y="10" width="60" height="30" rx="3" fill="#334155"/>
            <text x="75" y="28" fill="#94a3b8" font-size="9" text-anchor="middle">USB-MINI</text>
            <rect x="50" y="90" width="50" height="50" rx="4" fill="#0f172a"/>
            <text x="75" y="118" fill="#38bdf8" font-size="9" font-family="monospace" text-anchor="middle">NANO328</text>
            <text x="75" y="170" fill="#ffffff" font-size="14" font-weight="bold" text-anchor="middle">NANO</text>
            
            <!-- Left Header Silkscreen -->
            <g fill="#ffffff" font-size="9" font-family="monospace" font-weight="bold" text-anchor="start">
                <text x="30" y="38">D13</text><text x="30" y="53">3V3</text><text x="30" y="68">REF</text>
                <text x="30" y="83">A0</text><text x="30" y="98">A1</text><text x="30" y="113">A2</text>
                <text x="30" y="128">A3</text><text x="30" y="143">A4</text><text x="30" y="158">A5</text>
                <text x="30" y="173">A6</text><text x="30" y="188">A7</text><text x="30" y="203">5V</text>
            </g>
            <!-- Right Header Silkscreen -->
            <g fill="#ffffff" font-size="9" font-family="monospace" font-weight="bold" text-anchor="end">
                <text x="120" y="38">D12</text><text x="120" y="53">D11</text><text x="120" y="68">D10</text>
                <text x="120" y="83">D9</text><text x="120" y="98">D8</text><text x="120" y="113">D7</text>
                <text x="120" y="128">D6</text><text x="120" y="143">D5</text><text x="120" y="158">D4</text>
                <text x="120" y="173">D3</text><text x="120" y="188">D2</text><text x="120" y="203">GND</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 3. ESP32 DEVKIT V1
    // ----------------------------------------------------
    esp32: {
        name: "ESP32 DevKit V1",
        width: 170,
        height: 260,
        bg: "#18181b",
        pins: [
            { id: "EN", label: "EN", x: 18, y: 35, type: "power" },
            { id: "VP", label: "VP", x: 18, y: 50, type: "analog" },
            { id: "VN", label: "VN", x: 18, y: 65, type: "analog" },
            { id: "D34", label: "D34", x: 18, y: 80, type: "digital" },
            { id: "D35", label: "D35", x: 18, y: 95, type: "digital" },
            { id: "D32", label: "D32", x: 18, y: 110, type: "digital" },
            { id: "D33", label: "D33", x: 18, y: 125, type: "digital" },
            { id: "D25", label: "D25", x: 18, y: 140, type: "digital" },
            { id: "D26", label: "D26", x: 18, y: 155, type: "digital" },
            { id: "D27", label: "D27", x: 18, y: 170, type: "digital" },
            { id: "D14", label: "D14", x: 18, y: 185, type: "digital" },
            { id: "D12", label: "D12", x: 18, y: 200, type: "digital" },
            { id: "GND_L", label: "GND", x: 18, y: 215, type: "gnd" },

            { id: "3V3", label: "3V3", x: 152, y: 35, type: "power" },
            { id: "GND_R", label: "GND", x: 152, y: 50, type: "gnd" },
            { id: "D15", label: "D15", x: 152, y: 65, type: "digital" },
            { id: "D2", label: "D2", x: 152, y: 80, type: "digital" },
            { id: "D4", label: "D4", x: 152, y: 95, type: "digital" },
            { id: "RX2", label: "RX2", x: 152, y: 110, type: "serial" },
            { id: "TX2", label: "TX2", x: 152, y: 125, type: "serial" },
            { id: "D5", label: "D5", x: 152, y: 140, type: "digital" },
            { id: "D18", label: "D18", x: 152, y: 155, type: "digital" },
            { id: "D19", label: "D19", x: 152, y: 170, type: "digital" },
            { id: "D21", label: "D21", x: 152, y: 185, type: "digital" },
            { id: "D22", label: "D22", x: 152, y: 200, type: "digital" },
            { id: "D23", label: "D23", x: 152, y: 215, type: "digital" }
        ],
        svg: function(comp) {
            return `
            <rect width="170" height="260" rx="8" fill="#18181b" stroke="#3f3f46" stroke-width="3"/>
            <rect x="40" y="20" width="90" height="70" rx="4" fill="#71717a" stroke="#a1a1aa" stroke-width="2"/>
            <text x="85" y="55" fill="#18181b" font-size="12" font-weight="900" text-anchor="middle">ESP-WROOM-32</text>
            <text x="85" y="70" fill="#27272a" font-size="8" text-anchor="middle">Wi-Fi & Bluetooth</text>
            <text x="85" y="175" fill="#f43f5e" font-size="16" font-weight="bold" text-anchor="middle">ESP32</text>
            
            <!-- Left Header Silkscreen -->
            <g fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="start">
                <text x="30" y="38">EN</text><text x="30" y="53">VP</text><text x="30" y="68">VN</text>
                <text x="30" y="83">D34</text><text x="30" y="98">D35</text><text x="30" y="113">D32</text>
                <text x="30" y="128">D33</text><text x="30" y="143">D25</text><text x="30" y="158">D26</text>
                <text x="30" y="173">D27</text><text x="30" y="188">D14</text><text x="30" y="203">D12</text>
                <text x="30" y="218">GND</text>
            </g>
            <!-- Right Header Silkscreen -->
            <g fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="end">
                <text x="140" y="38">3V3</text><text x="140" y="53">GND</text><text x="140" y="68">D15</text>
                <text x="140" y="83">D2</text><text x="140" y="98">D4</text><text x="140" y="113">RX2</text>
                <text x="140" y="128">TX2</text><text x="140" y="143">D5</text><text x="140" y="158">D18</text>
                <text x="140" y="173">D19</text><text x="140" y="188">D21</text><text x="140" y="203">D22</text>
                <text x="140" y="218">D23</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 4. SENSOR ULTRASONIK HC-SR04
    // ----------------------------------------------------
    hc_sr04: {
        name: "HC-SR04 Ultrasonic",
        width: 180,
        height: 100,
        pins: [
            { id: "VCC", label: "VCC", x: 50, y: 85, type: "power" },
            { id: "TRIG", label: "Trig", x: 75, y: 85, type: "digital" },
            { id: "ECHO", label: "Echo", x: 100, y: 85, type: "digital" },
            { id: "GND", label: "GND", x: 125, y: 85, type: "gnd" }
        ],
        svg: function(comp) {
            const dist = comp.state?.distance || 50;
            return `
            <rect width="180" height="100" rx="8" fill="#0284c7" stroke="#0369a1" stroke-width="2"/>
            <circle cx="45" cy="40" r="28" fill="#334155" stroke="#94a3b8" stroke-width="3"/>
            <circle cx="45" cy="40" r="20" fill="#0f172a"/>
            <circle cx="135" cy="40" r="28" fill="#334155" stroke="#94a3b8" stroke-width="3"/>
            <circle cx="135" cy="40" r="20" fill="#0f172a"/>
            <text x="90" y="25" fill="#ffffff" font-size="10" font-weight="bold" text-anchor="middle">HC-SR04</text>
            <text x="90" y="55" fill="#38bdf8" font-size="12" font-weight="mono" text-anchor="middle">${dist} cm</text>
            
            <g fill="#ffffff" font-size="8" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="50" y="76">VCC</text>
                <text x="75" y="76">TRIG</text>
                <text x="100" y="76">ECHO</text>
                <text x="125" y="76">GND</text>
            </g>
            `;
        },
        controls: function(comp) {
            return `
            <div class="mt-2 p-2 bg-gray-900 rounded border border-gray-800 text-xs">
                <label class="text-gray-400 block mb-1">Simulasi Jarak Jangkauan:</label>
                <input type="range" min="2" max="400" value="${comp.state?.distance || 50}" 
                    oninput="SimLabEngine.updateCompState('${comp.id}', {distance: parseInt(this.value)})"
                    class="w-full accent-emerald-500 cursor-pointer"/>
            </div>
            `;
        }
    },

    // ----------------------------------------------------
    // 5. SENSOR SUHU & KELEMBABAN DHT11
    // ----------------------------------------------------
    dht11: {
        name: "DHT11 Sensor",
        width: 100,
        height: 130,
        pins: [
            { id: "VCC", label: "VCC", x: 20, y: 115, type: "power" },
            { id: "DATA", label: "DATA", x: 45, y: 115, type: "digital" },
            { id: "NC", label: "NC", x: 60, y: 115, type: "gnd" },
            { id: "GND", label: "GND", x: 80, y: 115, type: "gnd" }
        ],
        svg: function(comp) {
            const temp = comp.state?.temp || 28;
            const hum = comp.state?.humidity || 65;
            return `
            <rect width="100" height="130" rx="8" fill="#2563eb" stroke="#1d4ed8" stroke-width="2"/>
            <rect x="15" y="15" width="70" height="70" rx="4" fill="#1e40af" stroke="#60a5fa" stroke-width="1"/>
            <text x="50" y="45" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">${temp}°C</text>
            <text x="50" y="65" fill="#93c5fd" font-size="11" font-weight="bold" text-anchor="middle">${hum}% RH</text>
            <text x="50" y="100" fill="#ffffff" font-size="10" font-weight="mono" text-anchor="middle">DHT11</text>
            
            <g fill="#ffffff" font-size="7.5" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="20" y="107">VCC</text>
                <text x="45" y="107">DAT</text>
                <text x="60" y="107">NC</text>
                <text x="80" y="107">GND</text>
            </g>
            `;
        },
        controls: function(comp) {
            return `
            <div class="mt-2 p-2 bg-gray-900 rounded border border-gray-800 text-xs space-y-2">
                <div>
                    <label class="text-gray-400 block mb-1">Suhu (${comp.state?.temp || 28}°C):</label>
                    <input type="range" min="-10" max="60" value="${comp.state?.temp || 28}" 
                        oninput="SimLabEngine.updateCompState('${comp.id}', {temp: parseInt(this.value)})"
                        class="w-full accent-rose-500 cursor-pointer"/>
                </div>
                <div>
                    <label class="text-gray-400 block mb-1">Kelembaban (${comp.state?.humidity || 65}%):</label>
                    <input type="range" min="10" max="95" value="${comp.state?.humidity || 65}" 
                        oninput="SimLabEngine.updateCompState('${comp.id}', {humidity: parseInt(this.value)})"
                        class="w-full accent-blue-500 cursor-pointer"/>
                </div>
            </div>
            `;
        }
    },

    // ----------------------------------------------------
    // 6. MOTOR SERVO SG90
    // ----------------------------------------------------
    servo: {
        name: "Motor Servo SG90",
        width: 140,
        height: 140,
        pins: [
            { id: "GND", label: "GND (Cokelat)", x: 40, y: 125, type: "gnd" },
            { id: "VCC", label: "VCC (Merah)", x: 70, y: 125, type: "power" },
            { id: "PWM", label: "PWM (Oranye)", x: 100, y: 125, type: "digital" }
        ],
        svg: function(comp) {
            const angle = comp.state?.angle || 90;
            return `
            <rect width="140" height="140" rx="8" fill="#1d4ed8" stroke="#1e40af" stroke-width="2"/>
            <circle cx="70" cy="50" r="35" fill="#f8fafc" stroke="#cbd5e1" stroke-width="2"/>
            <g transform="rotate(${angle}, 70, 50)">
                <rect x="64" y="20" width="12" height="40" rx="6" fill="#ef4444"/>
                <circle cx="70" cy="50" r="6" fill="#1e293b"/>
            </g>
            <text x="70" y="105" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">SERVO SG90</text>
            <text x="70" y="118" fill="#93c5fd" font-size="10" font-weight="mono" text-anchor="middle">${angle}°</text>
            
            <g fill="#ffffff" font-size="7.5" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="40" y="116">GND</text>
                <text x="70" y="116">VCC</text>
                <text x="100" y="116">PWM</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 7. DISPLAY LCD 16x2 I2C
    // ----------------------------------------------------
    lcd1602: {
        name: "LCD 16x2 I2C",
        width: 280,
        height: 120,
        pins: [
            { id: "GND", label: "GND", x: 220, y: 105, type: "gnd" },
            { id: "VCC", label: "VCC", x: 235, y: 105, type: "power" },
            { id: "SDA", label: "SDA", x: 250, y: 105, type: "digital" },
            { id: "SCL", label: "SCL", x: 265, y: 105, type: "digital" }
        ],
        svg: function(comp) {
            const line1 = comp.state?.line1 || "  PembdaHUB    ";
            const line2 = comp.state?.line2 || "   SimLab v1.0  ";
            return `
            <rect width="280" height="120" rx="8" fill="#15803d" stroke="#166534" stroke-width="3"/>
            <rect x="25" y="15" width="230" height="70" rx="4" fill="#042f2e" stroke="#115e59" stroke-width="2"/>
            <rect x="35" y="23" width="210" height="54" rx="2" fill="#065f46"/>
            <text x="45" y="45" fill="#a7f3d0" font-size="14" font-family="monospace" font-weight="bold">${line1.padEnd(16, ' ')}</text>
            <text x="45" y="67" fill="#a7f3d0" font-size="14" font-family="monospace" font-weight="bold">${line2.padEnd(16, ' ')}</text>
            <text x="100" y="105" fill="#ffffff" font-size="11" font-weight="bold">LCD 16x2 I2C</text>
            
            <g fill="#ffffff" font-size="7" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="220" y="97">GND</text>
                <text x="235" y="97">VCC</text>
                <text x="250" y="97">SDA</text>
                <text x="265" y="97">SCL</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 8. RELAY 5V (1-CHANNEL)
    // ----------------------------------------------------
    relay: {
        name: "Relay 5V",
        width: 120,
        height: 120,
        pins: [
            { id: "VCC", label: "VCC", x: 30, y: 105, type: "power" },
            { id: "GND", label: "GND", x: 60, y: 105, type: "gnd" },
            { id: "IN", label: "IN", x: 90, y: 105, type: "digital" }
        ],
        svg: function(comp) {
            const active = comp.state?.active || false;
            return `
            <rect width="120" height="120" rx="8" fill="#1e293b" stroke="#334155" stroke-width="2"/>
            <rect x="25" y="20" width="70" height="60" rx="4" fill="${active ? '#15803d' : '#0369a1'}"/>
            <text x="60" y="55" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">RELAY 5V</text>
            <circle cx="60" cy="30" r="5" fill="${active ? '#22c55e' : '#ef4444'}"/>
            
            <g fill="#ffffff" font-size="8" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="30" y="96">VCC</text>
                <text x="60" y="96">GND</text>
                <text x="90" y="96">IN</text>
            </g>
            `;
        }
    },

    // ----------------------------------------------------
    // 9. LED INDIKATOR (MERAH, HIJAU, KUNING, PUTIH)
    // ----------------------------------------------------
    led_red: {
        name: "LED Merah",
        width: 60,
        height: 100,
        pins: [
            { id: "ANODE", label: "Anoda (+)", x: 20, y: 85, type: "digital" },
            { id: "CATHODE", label: "Katoda (-)", x: 40, y: 85, type: "gnd" }
        ],
        svg: function(comp) {
            const lit = comp.state?.lit || false;
            return `
            <rect width="60" height="100" rx="6" fill="#18181b" stroke="#27272a"/>
            <circle cx="30" cy="35" r="20" fill="${lit ? '#ef4444' : '#7f1d1d'}" stroke="${lit ? '#fca5a5' : '#991b1b'}" stroke-width="2"/>
            ${lit ? '<circle cx="30" cy="35" r="25" fill="#ef4444" opacity="0.3"/>' : ''}
            <text x="30" y="70" fill="#fca5a5" font-size="10" font-weight="bold" text-anchor="middle">LED</text>
            <text x="20" y="97" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">+</text>
            <text x="40" y="97" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">-</text>
            `;
        }
    },
    led_green: {
        name: "LED Hijau",
        width: 60,
        height: 100,
        pins: [
            { id: "ANODE", label: "Anoda (+)", x: 20, y: 85, type: "digital" },
            { id: "CATHODE", label: "Katoda (-)", x: 40, y: 85, type: "gnd" }
        ],
        svg: function(comp) {
            const lit = comp.state?.lit || false;
            return `
            <rect width="60" height="100" rx="6" fill="#18181b" stroke="#27272a"/>
            <circle cx="30" cy="35" r="20" fill="${lit ? '#22c55e' : '#14532d'}" stroke="${lit ? '#86efac' : '#166534'}" stroke-width="2"/>
            ${lit ? '<circle cx="30" cy="35" r="25" fill="#22c55e" opacity="0.3"/>' : ''}
            <text x="30" y="70" fill="#86efac" font-size="10" font-weight="bold" text-anchor="middle">LED</text>
            <text x="20" y="97" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">+</text>
            <text x="40" y="97" fill="#ffffff" font-size="7" font-weight="bold" text-anchor="middle">-</text>
            `;
        }
    },

    // ----------------------------------------------------
    // 10. RESISTOR (DEFAULT 300 OHM - FLEKSIBEL)
    // ----------------------------------------------------
    resistor: {
        name: "Resistor",
        width: 110,
        height: 50,
        pins: [
            { id: "PIN_1", label: "Pin 1", x: 10, y: 25, type: "passive" },
            { id: "PIN_2", label: "Pin 2", x: 100, y: 25, type: "passive" }
        ],
        svg: function(comp) {
            const val = comp.state?.ohms || 300;
            return `
            <line x1="10" y1="25" x2="30" y2="25" stroke="#94a3b8" stroke-width="3"/>
            <rect x="30" y="15" width="50" height="20" rx="3" fill="#d97706" stroke="#b45309"/>
            <rect x="38" y="15" width="4" height="20" fill="#ea580c"/>
            <rect x="48" y="15" width="4" height="20" fill="#000000"/>
            <rect x="58" y="15" width="4" height="20" fill="#78350f"/>
            <rect x="68" y="15" width="4" height="20" fill="#eab308"/>
            <line x1="80" y1="25" x2="100" y2="25" stroke="#94a3b8" stroke-width="3"/>
            <text x="55" y="46" fill="#fef3c7" font-size="9" font-weight="bold" text-anchor="middle">${val}Ω</text>
            `;
        },
        controls: function(comp) {
            const current = comp.state?.ohms || 300;
            return `
            <div class="mt-2 p-1.5 bg-gray-900 rounded border border-gray-800 text-[11px]">
                <label class="text-gray-400 block mb-1">Nilai Resistansi:</label>
                <select onchange="SimLabEngine.updateCompState('${comp.id}', {ohms: parseInt(this.value)})" class="w-full bg-gray-950 text-amber-400 font-mono text-xs rounded border border-gray-700 p-1">
                    <option value="100" ${current == 100 ? 'selected' : ''}>100 Ω</option>
                    <option value="220" ${current == 220 ? 'selected' : ''}>220 Ω</option>
                    <option value="300" ${current == 300 ? 'selected' : ''}>300 Ω (Default)</option>
                    <option value="1000" ${current == 1000 ? 'selected' : ''}>1 kΩ (1000 Ω)</option>
                    <option value="10000" ${current == 10000 ? 'selected' : ''}>10 kΩ (10000 Ω)</option>
                </select>
            </div>
            `;
        }
    },

    // ----------------------------------------------------
    // 11. POWER SUPPLY 5V / 3.3V 2A
    // ----------------------------------------------------
    psu: {
        name: "Power Supply Unit",
        width: 140,
        height: 100,
        pins: [
            { id: "VCC_5V", label: "+5V (2A)", x: 30, y: 85, type: "power" },
            { id: "VCC_3V3", label: "+3.3V (2A)", x: 70, y: 85, type: "power" },
            { id: "GND", label: "GND", x: 110, y: 85, type: "gnd" }
        ],
        svg: function(comp) {
            return `
            <rect width="140" height="100" rx="8" fill="#7f1d1d" stroke="#991b1b" stroke-width="2"/>
            <text x="70" y="30" fill="#ffffff" font-size="12" font-weight="bold" text-anchor="middle">POWER SUPPLY</text>
            <text x="70" y="50" fill="#fca5a5" font-size="10" font-weight="bold" text-anchor="middle">5V & 3.3V (2A Max)</text>
            <g fill="#ffffff" font-size="8" font-family="monospace" font-weight="bold" text-anchor="middle">
                <text x="30" y="76">5V</text>
                <text x="70" y="76">3.3V</text>
                <text x="110" y="76">GND</text>
            </g>
            `;
        }
    }
};
