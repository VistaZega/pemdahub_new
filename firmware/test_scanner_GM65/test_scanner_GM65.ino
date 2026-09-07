#include <SoftwareSerial.h>

// Pin D3 = GPIO0 (Menerima data dari pin TX GM65)
// Pin D4 = GPIO2 (Dummy)
SoftwareSerial gm65(0, 2); 

void setup() {
  // Komunikasi ke Serial Monitor Laptop (115200 baud)
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n=================================");
  Serial.println("   TES SCANNER GM65 (PIN D3)     ");
  Serial.println("=================================");
  Serial.println("Silakan scan Barcode / QR Code apa saja...");

  // Komunikasi ke modul GM65 (9600 baud)
  gm65.begin(9600);
}

void loop() {
  // Jika GM65 mengirim karakter, langsung tampilkan di Serial Monitor
  if (gm65.available()) {
    char c = gm65.read();
    Serial.write(c);
  }
}