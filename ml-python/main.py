from fastapi import FastAPI, HTTPException, Request
from pydantic import BaseModel, conlist
from typing import List
import joblib
import pandas as pd
import numpy as np
import os
import warnings
from fastapi.middleware.cors import CORSMiddleware

warnings.filterwarnings("ignore", category=UserWarning)

app = FastAPI(title="SP-Mental ML Service", version="2.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["POST"],
    allow_headers=["*"],
)

API_KEY_PASKIBRA = os.getenv("API_KEY_PASKIBRA", "secret_token_paskibra")
MODEL_PATH = os.path.join(os.path.dirname(__file__), "model", "model_mental_gaussian.pkl")

class PredictRequest(BaseModel):
    features: conlist(float, min_items=15, max_items=15)
    api_key: str

model = None
try:
    if os.path.exists(MODEL_PATH):
        model = joblib.load(MODEL_PATH)
        print("✅ Model loaded")
    else:
        print("⚠️ Model not found")
except Exception as e:
    print(f"❌ Load error: {e}")

@app.post("/predict")
async def predict(request: PredictRequest):
    if request.api_key != API_KEY_PASKIBRA:
        raise HTTPException(status_code=403, detail="Forbidden")

    if model is None:
        raise HTTPException(status_code=500, detail="Model uninitialized")

    try:
        feature_names = [f"G{i+1:02d}" for i in range(15)]
        X = pd.DataFrame([request.features], columns=feature_names)
        
        prediction = model.predict(X)[0]
        proba = model.predict_proba(X)[0]
        
        prob_dict = {str(cls): round(float(p) * 100, 2) for cls, p in zip(model.classes_, proba)}
        confidence = max(prob_dict.values())

        total_score = sum(request.features)
        if total_score < 25:
            kategori = "Sehat"
        elif 25 <= total_score < 32:
            kategori = "Ringan"
        else:
            kategori = "Stres Berat"

        return {
            "status": "success",
            "data": {
                "prediction": str(prediction),
                "kategori": kategori,
                "confidence": confidence,
                "probability": prob_dict,
                "total_score": total_score
            }
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/health")
def health_check():
    return {"status": "alive", "model_loaded": model is not None}
