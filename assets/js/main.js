// assets/js/main.js
const btnRefresh = document.getElementById('btnRefresh');
const btnSave = document.getElementById('btnSave');

function updateLocalTime(){
  const el = document.getElementById('localTime');
  const now = new Date();
  el.textContent = now.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
}
updateLocalTime();
setInterval(updateLocalTime, 1000);

async function fetchBMKG(save = false){
  try {
    // server-side fetch (safer than client-side calling BMKG directly)
    const url = API.getBmkg + (save ? '?save=1' : '');
    const res = await fetch(url);
    const j = await res.json();
    if (!j.success) {
      console.error('BMKG fetch error', j);
      return null;
    }
    return j.data;
  } catch (e) {
    console.error('fetchBMKG err', e);
    return null;
  }
}

function aiPredict(suhu, hum, deskripsi){
  // rule-based scoring
  let score = 0;
  const d = (deskripsi || '').toLowerCase();

  if (hum >= 80) score += 45;
  else if (hum >= 70) score += 25;

  if (d.includes('hujan') || d.includes('rain') || d.includes('lebat')) score += 40;
  if (suhu !== null && suhu <= 24) score += 10;

  // normalize score
  if (score > 100) score = 100;

  let label = 'Rendah';
  let rec = 'Cuaca aman untuk menjemur.';
  if (score >= 70) { label = 'Tinggi'; rec = 'Rekomendasi: Tutup jemuran / siapkan indoor.'; }
  else if (score >= 40) { label = 'Sedang'; rec = 'Waspada: Siapkan tindakan cepat.'; }

  return {score, label, rec};
}

function renderHistory(rows){
  if (!rows || rows.length === 0) {
    document.getElementById('historyWrap').innerHTML = '<div style="color:var(--muted)">Belum ada data tersimpan.</div>';
    return;
  }
  let html = '<table><thead><tr><th>Waktu</th><th>Suhu (°C)</th><th>Hum (%)</th><th>Deskripsi</th></tr></thead><tbody>';
  rows.forEach(r => {
    html += `<tr>
      <td>${r.created_at}</td>
      <td>${r.suhu ?? '-'}</td>
      <td>${r.kelembapan ?? '-'}</td>
      <td>${r.deskripsi ?? '-'}</td>
    </tr>`;
  });
  html += '</tbody></table>';
  document.getElementById('historyWrap').innerHTML = html;
}

async function loadHistory(){
  try {
    const res = await fetch(API.getHistory + '?limit=8');
    const j = await res.json();
    if (j.success) renderHistory(j.data);
    else renderHistory([]);
  } catch (e) {
    console.error('history err', e);
    renderHistory([]);
  }
}

async function refreshAll(saveSnapshot=false){
  const data = await fetchBMKG(saveSnapshot);
  if (!data) return;

  // data: adm4, waktu_data, suhu, kelembapan, deskripsi
  document.getElementById('bmkgTemp').textContent = (data.suhu ?? '-') + ' °C';
  document.getElementById('bmkgHum').textContent = (data.kelembapan ?? '-') + ' %';
  document.getElementById('bmkgDesc').textContent = (data.deskripsi ?? '-');
  document.getElementById('bmkgTime').textContent = 'Diambil: ' + (data.waktu_data ?? new Date().toLocaleString());

  // AI
  const suhu = parseFloat(data.suhu) || null;
  const hum = parseFloat(data.kelembapan) || 0;
  const ai = aiPredict(suhu, hum, data.deskripsi);
  document.getElementById('aiAnalysis').textContent = `Suhu: ${suhu ?? '-'} °C — Hum: ${hum}% — Deskripsi: ${data.deskripsi ?? '-'}`;
  document.getElementById('aiPred').textContent = `${ai.label} (Skor ${ai.score})`;
  document.getElementById('aiRec').textContent = ai.rec;

  // update history view (fresh)
  await loadHistory();
}

// events
btnRefresh.addEventListener('click', () => refreshAll(false));
btnSave.addEventListener('click', () => {
  btnSave.disabled = true;
  btnSave.textContent = 'Menyimpan...';
  refreshAll(true).finally(() => {
    btnSave.disabled = false;
    btnSave.textContent = 'Simpan Snapshot';
  });
});

// Fungsi untuk mengambil data sensor
async function fetchSensorData() {
  try {
    const res = await fetch(API.getSensor + '?t=' + Date.now()); // Prevent cache
    const j = await res.json();
    if (j.success) {
      updateSensorGrid(j.data);
      console.log('Data sensor updated:', j.data);
    }
  } catch (e) {
    console.error('Sensor fetch error', e);
  }
}

// Fungsi untuk update sensor grid
function updateSensorGrid(data) {
  console.log('Raw sensor data:', data);
  
  // Update nilai sensor
  document.getElementById('suhuValue').textContent = (data.suhu ?? '--') + ' °C';
  document.getElementById('humValue').textContent = (data.kelembapan ?? '--') + ' %';
  document.getElementById('ldrValue').textContent = (data.cahaya ?? '--') + ' lux';
  
  // Update sensor hujan realtime - perbaiki logika
  const rainValue = parseInt(data.hujan);
  const isRaining = rainValue === 1;
  
  console.log('Rain sensor - Raw:', data.hujan, 'Parsed:', rainValue, 'IsRaining:', isRaining);
  
  document.getElementById('rainValue').textContent = isRaining ? 'Basah' : 'Kering';
  
  const rainStatus = document.querySelector('#rainValue').nextElementSibling;
  rainStatus.textContent = isRaining ? 'Hujan Terdeteksi' : 'Tidak Hujan';
  rainStatus.className = isRaining ? 'sensor-status danger' : 'sensor-status normal';
  
  // Update status sensor lainnya berdasarkan nilai
  updateSensorStatus('suhu', data.suhu);
  updateSensorStatus('kelembapan', data.kelembapan);
  updateSensorStatus('cahaya', data.cahaya);
  
  // Update servo status
  updateServoStatus(data);
}

// Fungsi untuk update status sensor berdasarkan nilai
function updateSensorStatus(type, value) {
  let statusElement, statusClass, statusText;
  
  if (type === 'suhu') {
    statusElement = document.querySelector('#suhuValue').nextElementSibling;
    if (value < 20) { statusClass = 'sensor-status warning'; statusText = 'Dingin'; }
    else if (value > 35) { statusClass = 'sensor-status danger'; statusText = 'Panas'; }
    else { statusClass = 'sensor-status normal'; statusText = 'Normal'; }
  } else if (type === 'kelembapan') {
    statusElement = document.querySelector('#humValue').nextElementSibling;
    if (value < 30) { statusClass = 'sensor-status warning'; statusText = 'Kering'; }
    else if (value > 80) { statusClass = 'sensor-status danger'; statusText = 'Lembap'; }
    else { statusClass = 'sensor-status normal'; statusText = 'Normal'; }
  } else if (type === 'cahaya') {
    statusElement = document.querySelector('#ldrValue').nextElementSibling;
    if (value < 100) { statusClass = 'sensor-status warning'; statusText = 'Gelap'; }
    else if (value > 800) { statusClass = 'sensor-status normal'; statusText = 'Terang'; }
    else { statusClass = 'sensor-status normal'; statusText = 'Normal'; }
  }
  
  if (statusElement) {
    statusElement.className = statusClass;
    statusElement.textContent = statusText;
  }
}

// Fungsi untuk update status servo
function updateServoStatus(data) {
  const servoCards = document.querySelectorAll('.servo-card');
  
  // Update status pintu
  const statusPintu = data.status_pintu === 'TERBUKA' ? 'Terbuka' : 'Tertutup';
  servoCards[0].querySelector('.status-value').textContent = statusPintu;
  
  // Update status jemuran  
  const statusJemuran = data.status_jemuran === 'TERBUKA' ? 'Terbuka' : 'Tertutup';
  servoCards[1].querySelector('.status-value').textContent = statusJemuran;
  
  console.log('Status update:', { pintu: statusPintu, jemuran: statusJemuran });
}

// Fungsi kontrol servo
async function controlServo(servo, action) {
  try {
    console.log(`Mengirim perintah: ${servo} ${action}`);
    const res = await fetch(`${API.controlServo}?servo=${servo}&action=${action}`);
    const j = await res.json();
    console.log('Response servo control:', j);
    if (j.success) {
      console.log(`Berhasil ${action} ${servo}`);
      setTimeout(() => fetchSensorData(), 500); // Delay refresh
    } else {
      console.error('Servo control failed:', j.error);
    }
  } catch (e) {
    console.error('Servo control error', e);
  }
}

// Event listeners untuk tombol servo
document.addEventListener('DOMContentLoaded', () => {
  const servoCards = document.querySelectorAll('.servo-card');
  
  servoCards.forEach((card, index) => {
    const servo = index === 0 ? 'pintu' : 'jemuran';
    const buttons = card.querySelectorAll('.btn-control');
    
    buttons[0].onclick = () => {
      console.log(`Membuka ${servo}`);
      controlServo(servo, 'open');
    };
    buttons[2].onclick = () => {
      console.log(`Menutup ${servo}`);
      controlServo(servo, 'close');
    };
  });
});

// initial load
refreshAll(false);
fetchSensorData();

// Realtime update yang lebih agresif untuk servo status
setInterval(() => {
  fetchSensorData(); // Update sensor + servo setiap 1 detik
}, 1000);

// BMKG update lebih jarang
setInterval(() => {
  refreshAll(false);
}, 300000); // 5 menit