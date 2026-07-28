/**
 * PembdaHUB SimLab - Component Definitions Library
 * Mendefinisikan SVG visual, letak Pin, dan tipe interaktivitas untuk 16+ Komponen Elektronika & Board.
 */

window.SimLabComponents = {
    // ----------------------------------------------------
    // 1. ARDUINO UNO (ATmega328P) - Authentic Physical Board Layout (360x240)
    // Presisi Bebas Overlap & Arah Teks Sesuai Papan Fisik Asli
    // ----------------------------------------------------
    uno: {
        name: "Arduino Uno",
        width: 360,
        height: 240,
        bg: "#1e40af",
        pins: [
            // Top Digital Pins (y=18)
            { id: "SCL", label: "SCL", x: 130, y: 18, type: "digital" },
            { id: "SDA", label: "SDA", x: 142, y: 18, type: "digital" },
            { id: "AREF", label: "AREF", x: 154, y: 18, type: "analog" },
            { id: "GND_TOP", label: "GND", x: 166, y: 18, type: "gnd" },
            { id: "D13", label: "13", x: 178, y: 18, type: "digital", pwm: true },
            { id: "D12", label: "12", x: 190, y: 18, type: "digital" },
            { id: "D11", label: "~11", x: 202, y: 18, type: "digital", pwm: true },
            { id: "D10", label: "~10", x: 214, y: 18, type: "digital", pwm: true },
            { id: "D9", label: "~9", x: 226, y: 18, type: "digital", pwm: true },
            { id: "D8", label: "8", x: 238, y: 18, type: "digital" },
            
            { id: "D7", label: "7", x: 256, y: 18, type: "digital" },
            { id: "D6", label: "~6", x: 268, y: 18, type: "digital", pwm: true },
            { id: "D5", label: "~5", x: 280, y: 18, type: "digital", pwm: true },
            { id: "D4", label: "4", x: 292, y: 18, type: "digital" },
            { id: "D3", label: "~3", x: 304, y: 18, type: "digital", pwm: true },
            { id: "D2", label: "2", x: 316, y: 18, type: "digital" },
            { id: "TX", label: "1 (TX)", x: 328, y: 18, type: "serial" },
            { id: "RX", label: "0 (RX)", x: 340, y: 18, type: "serial" },

            // Bottom Power & Analog Pins (y=221)
            { id: "IOREF", label: "IOREF", x: 170, y: 221, type: "power" },
            { id: "RESET", label: "RST", x: 182, y: 221, type: "power" },
            { id: "3V3", label: "3.3V", x: 194, y: 221, type: "power" },
            { id: "5V", label: "5V", x: 206, y: 221, type: "power" },
            { id: "GND_1", label: "GND", x: 218, y: 221, type: "gnd" },
            { id: "GND_2", label: "GND", x: 230, y: 221, type: "gnd" },
            { id: "VIN", label: "VIN", x: 242, y: 221, type: "power" },
            
            { id: "A0", label: "A0", x: 266, y: 221, type: "analog" },
            { id: "A1", label: "A1", x: 278, y: 221, type: "analog" },
            { id: "A2", label: "A2", x: 290, y: 221, type: "analog" },
            { id: "A3", label: "A3", x: 302, y: 221, type: "analog" },
            { id: "A4", label: "A4", x: 314, y: 221, type: "analog" },
            { id: "A5", label: "A5", x: 326, y: 221, type: "analog" }
        ],
        svg: function(comp) {
            return `
            <!-- PCB Body Royal Blue -->
            <rect width="360" height="240" rx="12" fill="#1e40af" stroke="#1d4ed8" stroke-width="3"/>
            
            <!-- USB B Port Silver (Top Left) -->
            <rect x="10" y="25" width="55" height="50" rx="4" fill="#cbd5e1" stroke="#94a3b8" stroke-width="2"/>
            <rect x="15" y="32" width="45" height="36" rx="2" fill="#64748b"/>
            <text x="37" y="54" fill="#f8fafc" font-size="9" font-weight="bold" text-anchor="middle">USB B</text>

            <!-- DC Barrel Jack Black (Bottom Left) -->
            <rect x="10" y="160" width="60" height="50" rx="4" fill="#0f172a" stroke="#1e293b" stroke-width="2"/>
            <circle cx="10" cy="185" r="12" fill="#000000"/>

            <!-- Red Reset Button (Top Left next to USB) -->
            <rect x="75" y="25" width="22" height="22" rx="3" fill="#cbd5e1" stroke="#94a3b8"/>
            <circle cx="86" cy="36" r="6" fill="#ef4444"/>

            <!-- Crystal Oscillator Silver (16.000 MHz) -->
            <rect x="92" y="65" width="36" height="18" rx="7" fill="#94a3b8" stroke="#e2e8f0" stroke-width="1.5"/>
            <text x="110" y="77" fill="#1e293b" font-size="7" font-family="monospace" font-weight="bold" text-anchor="middle">16.000</text>

            <!-- Main ATmega328P Chip (Shifted down to y=110 to avoid top pin text collision) -->
            <rect x="200" y="110" width="55" height="55" rx="5" fill="#0f172a" stroke="#334155" stroke-width="2"/>
            <circle cx="210" cy="120" r="3" fill="#334155"/>
            <text x="227" y="138" fill="#64748b" font-size="8" font-family="monospace" text-anchor="middle">ATMEGA</text>
            <text x="227" y="148" fill="#64748b" font-size="8" font-family="monospace" text-anchor="middle">328P</text>

            <!-- ICSP Header 2x3 Grid (Far Right) -->
            <rect x="295" y="115" width="22" height="32" rx="2" fill="#020617" stroke="#1e293b"/>
            <circle cx="301" cy="121" r="2" fill="#eab308"/><circle cx="311" cy="121" r="2" fill="#eab308"/>
            <circle cx="301" cy="131" r="2" fill="#eab308"/><circle cx="311" cy="131" r="2" fill="#eab308"/>
            <circle cx="301" cy="141" r="2" fill="#eab308"/><circle cx="311" cy="141" r="2" fill="#eab308"/>

            <!-- Branding Logos (Positioned cleanly below top pin text) -->
            <text x="227" y="78" fill="#ffffff" font-size="20" font-family="sans-serif" font-weight="900" text-anchor="middle">UNO</text>
            <text x="227" y="92" fill="#93c5fd" font-size="8" font-family="sans-serif" font-weight="bold" text-anchor="middle">DIGITAL PWM(~)</text>

            <text x="206" y="180" fill="#93c5fd" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">POWER</text>
            <text x="296" y="180" fill="#93c5fd" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">ANALOG IN</text>

            <!-- Status LEDs (L D13 & ON) -->
            <circle id="led_uno_13_${comp.id}" cx="165" cy="115" r="4" fill="#451a03" stroke="#78350f" stroke-width="1"/>
            <text x="175" y="118" fill="#cbd5e1" font-size="8">L (D13)</text>
            <circle id="led_uno_on_${comp.id}" cx="165" cy="130" r="4" fill="#15803d" stroke="#22c55e" stroke-width="1"/>
            <text x="175" y="133" fill="#cbd5e1" font-size="8">ON</text>

            <!-- Top Header Black Socket Bar (y=10, height=16) -->
            <rect x="122" y="10" width="226" height="16" rx="2" fill="#020617"/>

            <!-- Top Header Silkscreen Labels: rotate(90 30) (Mulai Y=28 berjalan KE BAWAH dari y=28 ke y=52, 0 Overlap!) -->
            <g fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="start">
                <text x="130" y="28" transform="rotate(90 130 28)">SCL</text>
                <text x="142" y="28" transform="rotate(90 142 28)">SDA</text>
                <text x="154" y="28" transform="rotate(90 154 28)">AREF</text>
                <text x="166" y="28" transform="rotate(90 166 28)">GND</text>
                <text x="178" y="28" transform="rotate(90 178 28)">13</text>
                <text x="190" y="28" transform="rotate(90 190 28)">12</text>
                <text x="202" y="28" transform="rotate(90 202 28)">~11</text>
                <text x="214" y="28" transform="rotate(90 214 28)">~10</text>
                <text x="226" y="28" transform="rotate(90 226 28)">~9</text>
                <text x="238" y="28" transform="rotate(90 238 28)">8</text>
                
                <text x="256" y="28" transform="rotate(90 256 28)">7</text>
                <text x="268" y="28" transform="rotate(90 268 28)">~6</text>
                <text x="280" y="28" transform="rotate(90 280 28)">~5</text>
                <text x="292" y="28" transform="rotate(90 292 28)">4</text>
                <text x="304" y="28" transform="rotate(90 304 28)">~3</text>
                <text x="316" y="28" transform="rotate(90 316 28)">2</text>
                <text x="328" y="28" transform="rotate(90 328 28)">TX-1</text>
                <text x="340" y="28" transform="rotate(90 340 28)">RX-0</text>
            </g>

            <!-- Bottom Header Black Socket Bar (y=213, height=16) -->
            <rect x="162" y="213" width="172" height="16" rx="2" fill="#020617"/>

            <!-- Bottom Power/Analog Silkscreen Pin Labels: rotate(-90 204) (DI ATAS socket bar, berjalan KE ATAS dari y=204 ke y=176, 0 Overlap dengan Lingkaran Pin!) -->
            <g fill="#ffffff" font-size="8.5" font-family="monospace" font-weight="bold" text-anchor="start">
                <text x="170" y="204" transform="rotate(-90 170 204)">IOREF</text>
                <text x="182" y="204" transform="rotate(-90 182 204)">RESET</text>
                <text x="194" y="204" transform="rotate(-90 194 204)">3.3V</text>
                <text x="206" y="204" transform="rotate(-90 206 204)">5V</text>
                <text x="218" y="204" transform="rotate(-90 218 204)">GND</text>
                <text x="230" y="204" transform="rotate(-90 230 204)">GND</text>
                <text x="242" y="204" transform="rotate(-90 242 204)">VIN</text>

                <text x="266" y="204" transform="rotate(-90 266 204)">A0</text>
                <text x="278" y="204" transform="rotate(-90 278 204)">A1</text>
                <text x="290" y="204" transform="rotate(-90 290 204)">A2</text>
                <text x="302" y="204" transform="rotate(-90 302 204)">A3</text>
                <text x="314" y="204" transform="rotate(-90 314 204)">A4</text>
                <text x="326" y="204" transform="rotate(-90 326 204)">A5</text>
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
                <text x="140" y="78">D2</text><text x="140" y="93">D4</text><text x="140" y="113">RX2</text>
                <text x="140" y="128">TX2</text><text x="140" y="143">D5</text><text x="140" y="158">D18</text>
                <text x="140" y="168">D19</text><text x="140" y="188">D21</text><text x="140" y="203">D22</text>
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
