import os
import shutil
import pandas as pd

# =====================================================================
# 1. FIXED ENVIRONMENT ANCHORING (Climbs out of 'python' to project root)
# =====================================================================
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

if os.path.basename(SCRIPT_DIR) in ["python", "crowd_risk_calculator"]:
    PROJECT_ROOT = os.path.dirname(SCRIPT_DIR)
else:
    PROJECT_ROOT = SCRIPT_DIR

print(f"[SYSTEM] Project Root Anchored At: {PROJECT_ROOT}")

INPUT_CSV_PATH = os.path.join(PROJECT_ROOT, "merged", "KSRTC_merged.csv")
MASTER_OUTPUT_PATH = os.path.join(PROJECT_ROOT, "merged", "KSRTC_Merged_Final.csv")

# =====================================================================
# 2. DATA LOADING & PREPROCESSING
# =====================================================================
print(f"[DATA] Loading dataset from: {INPUT_CSV_PATH}")
df = pd.read_csv(INPUT_CSV_PATH)
df.fillna(0, inplace=True)

# --- FEATURE ENGINEERING ---
time_score_map = {
    "2 AM - 6 AM": 1, "6 AM - 10 AM": 3, "10 AM - 2 PM": 3,
    "2 PM - 6 PM": 3, "6 PM - 10 PM": 2, "10 PM - 2 AM": 1,
}
df["Time_Score"] = df["Slot Label"].map(time_score_map)

# --- CROWD RISK SCORE CALCULATION ---
df["Crowd_Risk_Score"] = (
    df["Population_Millions"] * 0.3
    + df["Urban_Level_Num"] * 0.2
    + df["Interstate_Flag_Num"] * 0.2
    + df["Time_Score"] * 0.3
)

# --- RISK LEVEL CLASSIFICATION ---
def risk_level(score):
    if score > 2.9: return "Very High"
    elif score >= 2.2: return "High"
    elif score >= 2.0: return "Moderate"
    elif score >= 1.6: return "Medium"
    elif score >= 1.3: return "Low"
    else: return "Very Low"

df["Crowd_Risk_Level"] = df["Crowd_Risk_Score"].apply(risk_level)

# Save master copy
os.makedirs(os.path.dirname(MASTER_OUTPUT_PATH), exist_ok=True)
df.to_csv(MASTER_OUTPUT_PATH, index=False)
print(f"[MASTER] Primary dataset written to: {MASTER_OUTPUT_PATH}")

# =====================================================================
# 3. CORRECTED TARGET SYNCHRONIZATION (Matching your nested structure)
# =====================================================================
sync_targets = {
    "Crowd Risk Calculator Folder": os.path.join(PROJECT_ROOT, "crowd_risk_calculator", "KSRTC_Merged_Final.csv"),
    "Nested Frontend CSV Directory": os.path.join(PROJECT_ROOT, "crowd_risk_calculator", "frontend", "csv", "KSRTC_Merged_Final.csv")
}

print("\n[SYNC] Starting data sync across target directories...")
for target_name, absolute_path in sync_targets.items():
    try:
        os.makedirs(os.path.dirname(absolute_path), exist_ok=True)
        shutil.copy(MASTER_OUTPUT_PATH, absolute_path)
        print(f" -> SUCCESS: Copied to [{target_name}] -> {absolute_path}")
    except Exception as e:
        print(f" -> ERROR: Sync failed for [{target_name}]: {e}")

print("\n--- DATA PROCESSING AND PIPELINE SYNC COMPLETE ---")