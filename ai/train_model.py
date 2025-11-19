# train_model.py
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, roc_auc_score
import joblib
import os

# Config
DATA_CSV = "data.csv"   # kamu isi / export data sensor+bmkg ke file ini
MODEL_OUT = "model.joblib"

def preprocess(df):
    # Waktu ke datetime
    df['waktu'] = pd.to_datetime(df['waktu'])
    df = df.sort_values('waktu')

    # Features from time
    df['hour'] = df['waktu'].dt.hour
    df['dow'] = df['waktu'].dt.dayofweek

    # Lag feature: apakah hujan di record sebelumnya
    df['last_rain'] = df['hujan'].shift(1).fillna(0).astype(int)

    # Target: next hour rain? (shift -1)
    df['target'] = df['hujan'].shift(-1).fillna(0).astype(int)

    # Drop last row if target from shift is invalid
    df = df[:-1]

    # Select features
    X = df[['suhu','kelembapan','hour','dow','last_rain']].copy()
    y = df['target'].copy()
    return X, y, df

def main():
    if not os.path.exists(DATA_CSV):
        print(f"data.csv not found. Generate a dummy csv or export your sensor_data + bmkg_data into {DATA_CSV}")
        return

    df = pd.read_csv(DATA_CSV)
    X, y, df_full = preprocess(df)

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)

    model = RandomForestClassifier(n_estimators=200, random_state=42, n_jobs=-1)
    model.fit(X_train, y_train)

    # eval
    preds = model.predict(X_test)
    probs = model.predict_proba(X_test)[:,1]
    print(classification_report(y_test, preds))
    try:
        print("AUC:", roc_auc_score(y_test, probs))
    except:
        pass

    joblib.dump(model, MODEL_OUT)
    print("Model saved to", MODEL_OUT)

if __name__ == "__main__":
    main()
