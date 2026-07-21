import pandas as pd

district_df = pd.read_csv(r"data/District_Info.csv")
dest_map_df = pd.read_csv(r"data/Destination_to_District_Mapping.csv")
timetable_df = pd.read_csv(r"data/KSRTC_Timetable_ProfessionalSlots.csv")
time_slot_df = pd.read_csv(r"excel_work/Time_Slot_Mapping.csv")

duplicates = dest_map_df[dest_map_df.duplicated(subset=["Destination"], keep=False)]
print(duplicates)

dest_map_df_unique = dest_map_df.drop_duplicates(subset="Destination", keep='first')
print(dest_map_df_unique)

df1 = pd.merge(timetable_df[["From","Destination","Service Class","Via Place","Time"]],dest_map_df_unique, on="Destination", how="left")
print(df1.head())

df2 = pd.merge(df1, district_df[["District","State","Population_Millions","Interstate_Flag"]], on="District", how="left")
print(df2.head())

print(df2.columns)

print(df1.columns)

print(df2.info())

print(df2.describe())

print(pd.read_csv(r"merged/KSRTC_Merged.csv"))

merged_df = pd.read_csv(r"merged/KSRTC_Merged.csv")
print(merged_df.head())

print(merged_df.info())

print(merged_df.describe())

print(merged_df.duplicated().sum())

import matplotlib.pyplot as plt
import seaborn as sns

merged_df['Interstate_Flag'].value_counts().plot(kind='pie', autopct='%1.1f%%')
plt.show()

