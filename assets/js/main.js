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

// initial load
refreshAll(false);
setInterval(() => refreshAll(false), 10 * 60 * 1000); // auto refresh every 10 minutes