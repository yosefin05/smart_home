# Tambahkan di bagian atas kode Thonny kamu
import urequests
import ujson

# WhatsApp Configuration
FONNTE_TOKEN = "UtBysCqUsdgWuY4Y5EUa"  # Daftar di fonnte.com
WHATSAPP_TARGET = "6285604515336"    # Nomor WhatsApp target (format 628xxx)

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
            print("📱 WhatsApp notification sent!")
            return True
        else:
            print(f"❌ WhatsApp failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"WhatsApp error: {e}")
        return False

def notify_event(event_type, data=None):
    import time
    timestamp = time.localtime()
    time_str = f"{timestamp[3]:02d}:{timestamp[4]:02d} {timestamp[2]:02d}/{timestamp[1]:02d}"
    
    if event_type == "rain_detected":
        message = f"🌧️ *HUJAN TERDETEKSI!*\n⏰ {time_str}\n📊 Sensor: {data}\n🏠 Jemuran otomatis TERTUTUP"
    elif event_type == "weather_clear":
        message = f"☀️ *CUACA CERAH!*\n⏰ {time_str}\n📊 Sensor: {data}\n🏠 Jemuran otomatis TERBUKA"
    elif event_type == "nfc_access":
        message = f"🔓 *PINTU DIBUKA*\n⏰ {time_str}\n🎫 Akses via NFC Card"
    elif event_type == "manual_control":
        device = "PINTU" if data['device'] == 'pintu' else "JEMURAN"
        action = "DIBUKA" if data['action'] == 1 else "DITUTUP"
        message = f"📱 *KONTROL MANUAL*\n⏰ {time_str}\n🏠 {device} {action} dari Website"
    else:
        return False
    
    return send_whatsapp_notification(message)

# Modifikasi fungsi kontrol_jemuran() - tambahkan notifikasi
def kontrol_jemuran():
    global jemuran_tertutup, auto_mode, last_rain_state_change
    global servo_jemuran_pos, status_jemuran
    
    current_rain = read_rain_sensor()
    
    if not auto_mode and time.ticks_diff(time.ticks_ms(), last_rain_state_change) > 60000:
        auto_mode = True
        print("🔄 Auto mode jemuran diaktifkan kembali")
    
    sedang_hujan = current_rain < RAIN_THRESHOLD
    cuaca_aman = current_rain > RAIN_SAFE_THRESHOLD
    
    # HUJAN TERDETEKSI + NOTIFIKASI WHATSAPP
    if sedang_hujan and not jemuran_tertutup:
        if time.ticks_diff(time.ticks_ms(), last_rain_state_change) > RAIN_DEBOUNCE:
            print("\n⚠️⚠️⚠️ HUJAN TERDETEKSI! TUTUP PAKSA! ⚠️⚠️⚠️")
            
            set_servo_angle(servo_jemuran, 0)
            servo_jemuran_pos = 0
            status_jemuran = "TERTUTUP"
            jemuran_tertutup = True
            last_rain_state_change = time.ticks_ms()
            auto_mode = True
            
            # KIRIM NOTIFIKASI WHATSAPP
            notify_event("rain_detected", current_rain)
            
            for _ in range(3):
                buzzer.on()
                time.sleep_ms(200)
                buzzer.off()
                time.sleep_ms(150)
            
            send_to_database()
    
    # CUACA CERAH + NOTIFIKASI WHATSAPP
    elif cuaca_aman and jemuran_tertutup and auto_mode:
        if time.ticks_diff(time.ticks_ms(), last_rain_state_change) > RAIN_DEBOUNCE:
            print("\n☀️☀️☀️ CUACA CERAH! BUKA OTOMATIS ☀️☀️☀️")
            
            set_servo_angle(servo_jemuran, 180)
            servo_jemuran_pos = 180
            status_jemuran = "TERBUKA"
            jemuran_tertutup = False
            last_rain_state_change = time.ticks_ms()
            
            # KIRIM NOTIFIKASI WHATSAPP
            notify_event("weather_clear", current_rain)
            
            buzzer.on()
            time.sleep_ms(300)
            buzzer.off()
            
            send_to_database()

# Modifikasi kontrol_pintu() - tambahkan notifikasi NFC
def kontrol_pintu():
    global pintu_terbuka, pintu_open_time, servo_pintu_pos, status_pintu
    
    if not pintu_terbuka:
        (stat, tag_type) = rdr.request(rdr.REQIDL)
        if stat == rdr.OK:
            (stat, raw_uid) = rdr.anticoll()
            if stat == rdr.OK:
                print("🔓 Kartu NFC terdeteksi!")
                
                # KIRIM NOTIFIKASI WHATSAPP NFC
                notify_event("nfc_access")
                
                buzzer.on()
                time.sleep_ms(100)
                buzzer.off()
                
                set_servo_angle(servo_pintu, 90)
                servo_pintu_pos = 90
                status_pintu = "TERBUKA"
                pintu_terbuka = True
                pintu_open_time = time.ticks_ms()
                
                send_to_database()
    
    if pintu_terbuka and time.ticks_diff(time.ticks_ms(), pintu_open_time) >= PINTU_OPEN_DURATION:
        set_servo_angle(servo_pintu, 0)
        servo_pintu_pos = 0
        status_pintu = "TERTUTUP"
        pintu_terbuka = False