#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ESP32Servo.h>
#include <MFRC522.h>
#include <SPI.h>

// ============ WiFi Setup ============
const char* ssid = "SKOMDA SISWA";
const char* password = "";
const char* serverURL = "http://10.218.17.86/smarthome/save_sensor.php";
const char* servoURL = "http://10.218.17.86/smarthome/get_servo_command.php";

// ============ Pin Setup ============
#define DHTPIN 4
#define DHTTYPE DHT22
#define RAIN_PIN 34
#define LDR_PIN 35
#define SERVO_JEMURAN 12
#define SERVO_PINTU 13
#define LED_PIN 26
#define BUZZER_PIN 16

// ============ NFC Setup ============
#define SS_PIN 21
#define RST_PIN 22
MFRC522 mfrc522(SS_PIN, RST_PIN);

// ============ Objek ============
DHT dht(DHTPIN, DHTTYPE);
Servo servoJemuran;
Servo servoPintu;

// ============ Thresholds ============
int RAIN_THRESHOLD = 1000;
int RAIN_SAFE_THRESHOLD = 1500;
const int LDR_THRESHOLD = 2000;
const int LDR_HYSTERESIS = 100;

// ============ Variabel Sensor ============
float suhu = 0;
float kelembapan = 0;
int rainValue = 4095;
int rainValueDry = 4095;
int ldrValue = 0;
bool ledState = false;

// ============ Status Servo ============
String statusJemuran = "TERBUKA";
String statusPintu = "TERTUTUP";
int servoJemuranPos = 180;
int servoPintuPos = 0;

// ============ Variabel NFC ============
bool pintuTerbuka = false;
unsigned long pintuOpenTime = 0;
const unsigned long PINTU_OPEN_DURATION = 3000;

// ============ Timer ============
unsigned long lastSendTime = 0;
unsigned long lastServoCheck = 0;
const unsigned long SEND_INTERVAL = 5000;
const unsigned long SERVO_CHECK_INTERVAL = 1000;

// ============ State Tracking ============
bool jemuranTertutup = false;
unsigned long lastRainStateChange = 0;
const unsigned long RAIN_DEBOUNCE = 15000; // 15 detik untuk stabilitas
bool autoMode = true;
int lastWebServoJemuran = -1;
int lastWebServoPintu = -1;

// ============ FUNGSI WiFi ============
void connectWiFi() {
    Serial.println("\n================================");
    Serial.println("Menghubungkan ke WiFi...");
    Serial.print("SSID: ");
    Serial.println(ssid);
    
    WiFi.mode(WIFI_STA);
    WiFi.begin(ssid, password);
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 40) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    
    Serial.println();
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("================================");
        Serial.println("WiFi TERHUBUNG!");
        Serial.print("IP Address: ");
        Serial.println(WiFi.localIP());
        Serial.println("================================");
    } else {
        Serial.println("GAGAL TERHUBUNG KE WIFI!");
    }
}

void reconnectWiFi() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi TERPUTUS! Reconnecting...");
        WiFi.disconnect();
        WiFi.begin(ssid, password);
        
        int attempts = 0;
        while (WiFi.status() != WL_CONNECTED && attempts < 20) {
            delay(500);
            Serial.print(".");
            attempts++;
        }
        
        if (WiFi.status() == WL_CONNECTED) {
            Serial.println("\nWiFi RECONNECTED!");
        }
    }
}

// ============ FUNGSI BACA SENSOR HUJAN ============
int readRainSensor() {
    long sum = 0;
    for (int i = 0; i < 10; i++) {
        sum += analogRead(RAIN_PIN);
        delay(10);
    }
    return sum / 10;
}

// ============ FUNGSI KIRIM DATA KE DATABASE ============
void sendToDatabase() {
    reconnectWiFi();
    
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("❌ WiFi tidak terhubung - data tidak terkirim");
        return;
    }
    
    HTTPClient http;
    
    int hujanStatus = (rainValue < RAIN_THRESHOLD) ? 1 : 0;
    
    String url = String(serverURL) + 
        "?suhu=" + String(suhu, 2) +
        "&kelembapan=" + String(kelembapan, 2) +
        "&cahaya=" + String(ldrValue) +
        "&hujan=" + String(hujanStatus) +
        "&servo_jemuran=" + String(servoJemuranPos == 180 ? 1 : 0) +
        "&servo_pintu=" + String(servoPintuPos == 90 ? 1 : 0) +
        "&led=" + String(ledState ? 1 : 0) +
        "&status_jemuran=" + statusJemuran +
        "&status_pintu=" + statusPintu;
    
    http.begin(url);
    http.setTimeout(10000);
    
    int httpCode = http.GET();
    
    if (httpCode > 0) {
        String response = http.getString();
        if (response.indexOf("SUCCESS") >= 0) {
            Serial.println("✅ Data tersimpan ke database");
        }
    } else {
        Serial.printf("HTTP Error: %d\n", httpCode);
    }
    
    http.end();
}

// ============ FUNGSI CEK PERINTAH SERVO DARI WEB ============
void checkServoCommand() {
    if (WiFi.status() != WL_CONNECTED) return;
    
    HTTPClient http;
    http.begin(servoURL);
    http.setTimeout(3000);
    
    int httpCode = http.GET();
    
    if (httpCode == HTTP_CODE_OK) {
        String payload = http.getString();
        
        int firstComma = payload.indexOf(',');
        int secondComma = payload.indexOf(',', firstComma + 1);
        
        if (firstComma > 0 && secondComma > 0) {
            int webServoPintu = payload.substring(0, firstComma).toInt();
            int webServoJemuran = payload.substring(firstComma + 1, secondComma).toInt();
            
            // Kontrol Servo Pintu
            if (webServoPintu != lastWebServoPintu) {
                lastWebServoPintu = webServoPintu;
                
                if (webServoPintu == 1 && servoPintuPos != 90) {
                    Serial.println("🔓 MEMBUKA PINTU dari Web");
                    servoPintu.write(90);
                    servoPintuPos = 90;
                    statusPintu = "TERBUKA";
                    
                    digitalWrite(BUZZER_PIN, HIGH);
                    delay(100);
                    digitalWrite(BUZZER_PIN, LOW);
                    
                } else if (webServoPintu == 0 && servoPintuPos != 0) {
                    Serial.println("🔒 MENUTUP PINTU dari Web");
                    servoPintu.write(0);
                    servoPintuPos = 0;
                    statusPintu = "TERTUTUP";
                }
            }
            
            // Kontrol Servo Jemuran
            if (webServoJemuran != lastWebServoJemuran) {
                lastWebServoJemuran = webServoJemuran;
                
                if (webServoJemuran == 1 && servoJemuranPos != 180) {
                    Serial.println("☀️ MEMBUKA JEMURAN dari Web");
                    servoJemuran.write(180);
                    servoJemuranPos = 180;
                    statusJemuran = "TERBUKA";
                    jemuranTertutup = false;
                    autoMode = false;
                    lastRainStateChange = millis();
                    
                } else if (webServoJemuran == 0 && servoJemuranPos != 0) {
                    Serial.println("🌧️ MENUTUP JEMURAN dari Web");
                    servoJemuran.write(0);
                    servoJemuranPos = 0;
                    statusJemuran = "TERTUTUP";
                    jemuranTertutup = true;
                    autoMode = false;
                    lastRainStateChange = millis();
                }
            }
        }
    }
    
    http.end();
}

// ============ FUNGSI KONTROL JEMURAN AUTO ============
void kontrolJemuran() {
    int currentRain = readRainSensor();
    
    // Re-enable auto mode setelah 60 detik dari kontrol manual
    if (!autoMode && (millis() - lastRainStateChange > 60000)) {
        autoMode = true;
        Serial.println("🔄 Auto mode jemuran diaktifkan kembali");
    }
    
    bool sedangHujan = (currentRain < RAIN_THRESHOLD);
    bool cuacaAman = (currentRain > RAIN_SAFE_THRESHOLD);
    
    // PRIORITAS TINGGI: Tutup jika hujan (override manual)
    if (sedangHujan && !jemuranTertutup) {
        if (millis() - lastRainStateChange > RAIN_DEBOUNCE) {
            Serial.println("\n⚠️⚠️⚠️ HUJAN TERDETEKSI! TUTUP PAKSA! ⚠️⚠️⚠️");
            Serial.printf("Nilai sensor: %d (threshold: <%d)\n", currentRain, RAIN_THRESHOLD);
            
            servoJemuran.write(0);
            servoJemuranPos = 0;
            statusJemuran = "TERTUTUP";
            jemuranTertutup = true;
            lastRainStateChange = millis();
            autoMode = true; // Force auto mode untuk proteksi
            
            for(int i = 0; i < 3; i++) {
                digitalWrite(BUZZER_PIN, HIGH);
                delay(200);
                digitalWrite(BUZZER_PIN, LOW);
                delay(150);
            }
            
            sendToDatabase();
        }
    }
    // AUTO: Buka jemuran jika cuaca aman DAN auto mode aktif
    else if (cuacaAman && jemuranTertutup && autoMode) {
        if (millis() - lastRainStateChange > RAIN_DEBOUNCE) {
            Serial.println("\n☀️☀️☀️ CUACA CERAH! BUKA OTOMATIS ☀️☀️☀️");
            Serial.printf("Nilai sensor: %d (threshold: >%d)\n", currentRain, RAIN_SAFE_THRESHOLD);
            
            servoJemuran.write(180);
            servoJemuranPos = 180;
            statusJemuran = "TERBUKA";
            jemuranTertutup = false;
            lastRainStateChange = millis();
            
            digitalWrite(BUZZER_PIN, HIGH);
            delay(300);
            digitalWrite(BUZZER_PIN, LOW);
            
            sendToDatabase();
        }
    }
}

// ============ FUNGSI KONTROL PINTU AUTO ============
void kontrolPintu() {
    if (!pintuTerbuka && mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
        Serial.println("🔓 Kartu NFC terdeteksi!");
        
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
        
        servoPintu.write(90);
        servoPintuPos = 90;
        statusPintu = "TERBUKA";
        pintuTerbuka = true;
        pintuOpenTime = millis();
        
        sendToDatabase();
        mfrc522.PICC_HaltA();
    }
    
    if (pintuTerbuka && (millis() - pintuOpenTime >= PINTU_OPEN_DURATION)) {
        Serial.println("🔒 Menutup pintu otomatis");
        servoPintu.write(0);
        servoPintuPos = 0;
        statusPintu = "TERTUTUP";
        pintuTerbuka = false;
    }
}

// ============ SETUP ============
void setup() {
    Serial.begin(115200);
    delay(1000);
    
    Serial.println("\n╔════════════════════════════════════╗");
    Serial.println("║   SMART HOME HYBRID FIXED         ║");
    Serial.println("╚════════════════════════════════════╝\n");
    
    connectWiFi();
    
    dht.begin();
    SPI.begin();
    mfrc522.PCD_Init();
    
    servoJemuran.attach(SERVO_JEMURAN);
    servoPintu.attach(SERVO_PINTU);
    
    pinMode(LED_PIN, OUTPUT);
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(RAIN_PIN, INPUT);
    
    servoPintu.write(0);
    servoPintuPos = 0;
    statusPintu = "TERTUTUP";
    
    digitalWrite(LED_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
    
    // Kalibrasi sensor hujan
    Serial.println("╔════════════════════════════════════╗");
    Serial.println("║   KALIBRASI SENSOR HUJAN           ║");
    Serial.println("╚════════════════════════════════════╝");
    Serial.println("⚠️  PASTIKAN SENSOR KERING!");
    Serial.println("⏳  Mulai dalam 5 detik...\n");
    
    for(int i = 5; i > 0; i--) {
        Serial.printf("    %d...\n", i);
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
        delay(900);
    }
    
    Serial.println("🔍 Membaca sensor...\n");
    
    long sumDry = 0;
    for(int i = 0; i < 20; i++) {
        int val = readRainSensor();
        sumDry += val;
        Serial.printf("   [%2d] Nilai: %4d\n", i+1, val);
        delay(200);
    }
    
    rainValueDry = sumDry / 20;
    RAIN_THRESHOLD = rainValueDry * 0.25;  // Sangat sensitif untuk hujan
    RAIN_SAFE_THRESHOLD = rainValueDry * 0.90; // Sangat aman untuk buka
    
    Serial.println("\n╔════════════════════════════════════╗");
    Serial.println("║   HASIL KALIBRASI                  ║");
    Serial.println("╚════════════════════════════════════╝");
    Serial.printf("📊 Nilai Kering     : %d\n", rainValueDry);
    Serial.printf("⚙️  Threshold HUJAN  : < %d\n", RAIN_THRESHOLD);
    Serial.printf("⚙️  Threshold AMAN   : > %d\n", RAIN_SAFE_THRESHOLD);
    Serial.println();
    
    if(rainValueDry > 500) {
        Serial.println("✅ Kalibrasi berhasil!");
    } else {
        Serial.println("⚠️  WARNING: Sensor mungkin bermasalah!");
    }
    
    Serial.println("🔧 Set posisi awal jemuran TERBUKA...\n");
    servoJemuran.write(180);
    servoJemuranPos = 180;
    statusJemuran = "TERBUKA";
    jemuranTertutup = false;
    lastRainStateChange = millis();
    
    delay(1000);
    
    Serial.println("════════════════════════════════════");
    Serial.println("  SISTEM SIAP BEROPERASI! 🚀");
    Serial.println("════════════════════════════════════\n");
}

// ============ LOOP UTAMA ============
void loop() {
    // Baca sensor DHT
    float t = dht.readTemperature();
    float h = dht.readHumidity();
    if (!isnan(t)) suhu = t;
    if (!isnan(h)) kelembapan = h;
    
    // Baca sensor hujan
    rainValue = readRainSensor();
    
    // Baca LDR
    long sum = 0;
    for (int i = 0; i < 10; i++) {
        sum += analogRead(LDR_PIN);
        delay(5);
    }
    ldrValue = sum / 10;
    
    // Cek perintah servo dari web
    if (millis() - lastServoCheck >= SERVO_CHECK_INTERVAL) {
        checkServoCommand();
        lastServoCheck = millis();
    }
    
    // Kontrol jemuran (hybrid)
    kontrolJemuran();
    
    // Kontrol pintu (hybrid)
    kontrolPintu();
    
    // Kontrol LED otomatis
    if (ldrValue > LDR_THRESHOLD + LDR_HYSTERESIS && !ledState) {
        digitalWrite(LED_PIN, HIGH);
        ledState = true;
        Serial.println("💡 LED ON (Gelap)");
    } else if (ldrValue < LDR_THRESHOLD - LDR_HYSTERESIS && ledState) {
        digitalWrite(LED_PIN, LOW);
        ledState = false;
        Serial.println("💡 LED OFF (Terang)");
    }
    
    // Tampilkan data
    int rainPercent = 100 - map(rainValue, 0, rainValueDry, 0, 100);
    rainPercent = constrain(rainPercent, 0, 100);
    
    Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    Serial.printf("MODE: %s | T:H<%d S>%d\n", 
                  autoMode ? "AUTO" : "MANUAL", RAIN_THRESHOLD, RAIN_SAFE_THRESHOLD);
    Serial.printf("🌡️  Suhu: %.1f°C | 💧 Kelembapan: %.1f%%\n", suhu, kelembapan);
    Serial.printf("☔  Hujan: %d/%d [%d%%] ", rainValue, rainValueDry, rainPercent);
    
    if(rainValue < RAIN_THRESHOLD) {
        Serial.println("🔴 HUJAN");
    } else if(rainValue > RAIN_SAFE_THRESHOLD) {
        Serial.println("🟢 AMAN");
    } else {
        Serial.println("🟡 TENGAH");
    }
    
    Serial.printf("💡 Cahaya: %d | LED: %s\n", ldrValue, ledState ? "ON" : "OFF");
    Serial.printf("📟 Pintu: %s | Jemuran: %s\n", 
                  statusPintu.c_str(), statusJemuran.c_str());
    
    // Kirim data ke database
    if (millis() - lastSendTime >= SEND_INTERVAL) {
        sendToDatabase();
        lastSendTime = millis();
    }
    
    delay(1000);
}