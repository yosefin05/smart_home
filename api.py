from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
import numpy as np
import os

app = Flask(__name__)
CORS(app)

# Pastikan path model
MODEL_PATH = os.path.abspath("model_cuaca.pkl")
print("Loading model dari:", MODEL_PATH)

# Load model dengan try/except
try:
    model = pickle.load(open(MODEL_PATH, "rb"))
    print("Model berhasil diload!")
except Exception as e:
    model = None
    print("Gagal load model:", e)

# Mapping angka ke teks
label_map = {
    0: "Cerah",
    1: "Berawan",
    2: "Hujan"
}

@app.route("/prediksi", methods=["POST"])
def prediksi():
    if model is None:
        return jsonify({"hasil": "AI Offline"})

    try:
        data = request.json
        suhu = float(data.get("suhu", 0))
        kelembapan = float(data.get("kelembapan", 0))
        cahaya = float(data.get("cahaya", 0))

        X = np.array([[suhu, kelembapan, cahaya]])
        pred = model.predict(X)[0]

        # Pastikan pred ada di label_map
        hasil_text = label_map.get(int(pred), "Tidak Diketahui")
        return jsonify({"hasil": hasil_text})

    except Exception as e:
        print("Prediksi error:", e)
        return jsonify({"hasil": "AI Offline"})

if __name__ == "__main__":
    app.run(debug=True, port=5000)
