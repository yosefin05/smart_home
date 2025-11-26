# TAMBAHKAN IMPORT INI DI BAGIAN ATAS KODE THONNY KAMU
import urequests
import ujson

# TAMBAHKAN KONFIGURASI WHATSAPP
FONNTE_TOKEN = "UtBysCqUsdgWuY4Y5EUa"
WHATSAPP_TARGET = "6285604515336"

# TAMBAHKAN FUNGSI NOTIFIKASI INI
def send_whatsapp_notification(message):
    try:
        url = "https://api.fonnte.com/send"
        
        data = {
            'target': WHATSAPP_TARGET,
            'message': message,
            'countryCode': '62'
        }
        
        headers = {
            'Authorization': FONNTE_TOKEN,
            'Content-Type': 'application/json'
        }
        
        response = urequests.post(url, 
                                data=ujson.dumps(data), 
                                headers=headers,
                                timeout=10)
        
        if response.status_code == 200:
            print("📱 WhatsApp sent!")
            return True
        else:
            print(f"❌ WhatsApp failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"WhatsApp error: {e}")
        return False

def notify_whatsapp(event_type, data=None):
    import time
    timestamp = time.localtime()
    time_str = f"{timestamp[3]:02d}:{timestamp[4]:02d} {timestamp[2]:02d}/{timestamp[1]:02d}"
    
    if event_type == "rain":
        message = f"🌧️ *HUJAN TERDETEKSI!*\n⏰ {time_str}\n📊 Sensor: {data}\n🏠 Jemuran otomatis TERTUTUP"
    elif event_type == "clear":
        message = f"☀️ *CUACA CERAH!*\n⏰ {time_str}\n📊 Sensor: {data}\n🏠 Jemuran otomatis TERBUKA"
    elif event_type == "nfc":
        message = f"🔓 *PINTU DIBUKA*\n⏰ {time_str}\n🎫 Akses via NFC Card"
    else:
        return False
    
    return send_whatsapp_notification(message)

# MODIFIKASI FUNGSI kontrol_jemuran() KAMU - TAMBAHKAN BARIS INI:
def kontrol_jemuran():
    # ... kode yang sudah ada ...
    
    # HUJAN TERDETEKSI + NOTIFIKASI
    if sedang_hujan and not jemuran_tertutup:
        if time.ticks_diff(time.ticks_ms(), last_rain_state_change) > RAIN_DEBOUNCE:
            print("\n⚠️⚠️⚠️ HUJAN TERDETEKSI! TUTUP PAKSA! ⚠️⚠️⚠️")
            
            # ... kode servo ...
            
            # TAMBAHKAN BARIS INI:
            notify_whatsapp("rain", current_rain)
            
            # ... sisa kode ...
    
    # CUACA CERAH + NOTIFIKASI  
    elif cuaca_aman and jemuran_tertutup and auto_mode:
        if time.ticks_diff(time.ticks_ms(), last_rain_state_change) > RAIN_DEBOUNCE:
            print("\n☀️☀️☀️ CUACA CERAH! BUKA OTOMATIS ☀️☀️☀️")
            
            # ... kode servo ...
            
            # TAMBAHKAN BARIS INI:
            notify_whatsapp("clear", current_rain)
            
            # ... sisa kode ...

# MODIFIKASI FUNGSI kontrol_pintu() KAMU - TAMBAHKAN BARIS INI:
def kontrol_pintu():
    # ... kode yang sudah ada ...
    
    if not pintu_terbuka:
        (stat, tag_type) = rdr.request(rdr.REQIDL)
        if stat == rdr.OK:
            (stat, raw_uid) = rdr.anticoll()
            if stat == rdr.OK:
                print("🔓 Kartu NFC terdeteksi!")
                
                # TAMBAHKAN BARIS INI:
                notify_whatsapp("nfc")
                
                # ... sisa kode servo ...