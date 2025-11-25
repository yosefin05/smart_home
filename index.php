<?php
// index.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Home</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="title">
        <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#60a5fa,#3b82f6);display:flex;align-items:center;justify-content:center;color:white;font-weight:700">AI</div>
        <div>
          <h1>Smart Home Dashboard AI</h1>
          <div class="meta" id="metaLocation">Sekardangan, Sidoarjo — BMKG</div>
        </div>
      </div>
      <div>
        <div id="localTime" class="meta" style="text-align:right;color:rgba(255,255,255,0.85)"></div>
        <div style="margin-top:8px;text-align:right">
          <button class="btn" id="btnRefresh">Refresh</button>
          <button class="btn ghost" id="btnSave">Simpan Snapshot</button>
        </div>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div class="label">🌡️ Suhu BMKG</div>
        <div class="value" id="bmkgTemp">-- °C</div>
        <div class="sub" id="bmkgTime">—</div>
      </div>

      <div class="card">
        <div class="label">💧 Kelembapan BMKG</div>
        <div class="value" id="bmkgHum">-- %</div>
        <div class="sub">Sumber: BMKG (Prakiraan)</div>
      </div>

      <div class="card">
        <div class="label">🌦️ Deskripsi Cuaca</div>
        <div class="value" id="bmkgDesc">—</div>
        <div class="sub" id="bmkgExtra">—</div>
      </div>

      <div class="ai-card">
        <h2>🧠 Prediksi Hujan — AI (Rule-based)</h2>
        <div class="ai-row">
          <div class="ai-item">
            <div style="font-size:0.95rem;color:#374151">Analisis Saat Ini</div>
            <div style="margin-top:8px;font-weight:700" id="aiAnalysis">Memuat data...</div>
          </div>
          <div class="ai-item">
            <div style="font-size:0.95rem;color:#374151">Prediksi</div>
            <div style="margin-top:8px;font-weight:700" id="aiPred">—</div>
          </div>
          <div class="ai-item">
            <div style="font-size:0.95rem;color:#374151">Rekomendasi</div>
            <div style="margin-top:8px;font-weight:700" id="aiRec">—</div>
          </div>
        </div>

        <div class="history" style="margin-top:14px">
          <h3 style="color:#111827;margin-bottom:8px">History BMKG (terakhir)</h3>
          <div id="historyWrap">Memuat...</div>
        </div>
      </div>
    </div>
  </div>

  <div class="servo-section">
    <h2 class="section-title">Kontrol Servo</h2>

    <div class="servo-grid">
        
        <!-- SERVO 1 -->
        <div class="servo-card">
            <div class="servo-header">
                <div class="servo-info">
                    <span class="servo-icon">🚪</span>
                    <h3>Servo Pintu</h3>
                </div>
                <span class="servo-mode">AUTO</span>
            </div>

            <p class="servo-status">Status: <span class="status-value">Tertutup</span></p>

            <div class="servo-buttons">
                <button class="btn-control open">Buka</button>
                <button class="btn-control auto">Auto</button>
                <button class="btn-control close">Tutup</button>
            </div>
        </div>

        <!-- SERVO 2 -->
        <div class="servo-card">
            <div class="servo-header">
                <div class="servo-info">
                    <span class="servo-icon">👕</span>
                    <h3>Servo Jemuran</h3>
                </div>
                <span class="servo-mode">AUTO</span>
            </div>

            <p class="servo-status">Status: <span class="status-value">Terbuka</span></p>

            <div class="servo-buttons">
                <button class="btn-control open">Buka</button>
                <button class="btn-control auto">Auto</button>
                <button class="btn-control close">Tutup</button>
            </div>
        </div>

    </div>
</div>

<section class="sensor-grid">

  <div class="sensor-card">
      <div class="sensor-header">
          <span class="sensor-icon">🌡️</span>
          <h3>Suhu DHT22</h3>
      </div>
      <div class="sensor-value" id="suhuValue">-- °C</div>
      <span class="sensor-status normal">Normal</span>
  </div>

  <div class="sensor-card">
      <div class="sensor-header">
          <span class="sensor-icon">💧</span>
          <h3>Kelembapan DHT22</h3>
      </div>
      <div class="sensor-value" id="humValue">-- %</div>
      <span class="sensor-status normal">Normal</span>
  </div>

  <div class="sensor-card">
      <div class="sensor-header">
          <span class="sensor-icon">💡</span>
          <h3>Sensor LDR</h3>
      </div>
      <div class="sensor-value" id="ldrValue">-- lux</div>
      <span class="sensor-status normal">Normal</span>
  </div>

  <div class="sensor-card">
      <div class="sensor-header">
          <span class="sensor-icon">🌧️</span>
          <h3>Sensor Hujan</h3>
      </div>
      <div class="sensor-value" id="rainValue">--</div>
      <span class="sensor-status danger">Tidak Hujan</span>
  </div>

  <div class="sensor-card">
      <div class="sensor-header">
          <span class="sensor-icon">📟</span>
          <h3>NFC RC522</h3>
      </div>
      <div class="sensor-value">Tap Kartu</div>
      <span class="sensor-status standby">Standby</span>
  </div>
</section>


  <script>
    const BASE = '';
    const API = {
      getBmkg: 'api/get_bmkg.php',
      saveBmkg: 'api/save_bmkg.php',
      getHistory: 'api/get_history.php',
      getSensor: 'get_sensor.php',
      controlServo: 'control_servo.php'
    };
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>
