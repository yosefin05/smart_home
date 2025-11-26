import urequests
import ujson
import network
import time

# WiFi Setup
SSID = "SKOMDA SISWA"
PASSWORD = ""

# WhatsApp Config
FONNTE_TOKEN = "UtBysCqUsdgWuY4Y5EUa"
WHATSAPP_TARGET = "6285604515336"

# Connect WiFi
wlan = network.WLAN(network.STA_IF)
wlan.active(True)
wlan.connect(SSID, PASSWORD)

print("Connecting to WiFi...")
while not wlan.isconnected():
    time.sleep(0.5)
    print(".", end="")

print(f"\nWiFi Connected! IP: {wlan.ifconfig()[0]}")

# Test WhatsApp
def send_test_message():
    try:
        url = "https://api.fonnte.com/send"
        
        data = {
            'target': WHATSAPP_TARGET,
            'message': "🧪 *TEST WHATSAPP*\n⏰ " + str(time.localtime()) + "\n✅ Sistem Smart Home siap!\n📱 Notifikasi berhasil",
            'countryCode': '62'
        }
        
        headers = {
            'Authorization': FONNTE_TOKEN,
            'Content-Type': 'application/json'
        }
        
        print("Sending WhatsApp message...")
        response = urequests.post(url, 
                                data=ujson.dumps(data), 
                                headers=headers,
                                timeout=15)
        
        print(f"Status Code: {response.status_code}")
        print(f"Response: {response.text}")
        
        if response.status_code == 200:
            print("✅ SUCCESS! Cek WhatsApp kamu!")
        else:
            print("❌ FAILED! Cek token/nomor!")
            
        response.close()
        
    except Exception as e:
        print(f"Error: {e}")

# Run test
send_test_message()
print("Test selesai!")