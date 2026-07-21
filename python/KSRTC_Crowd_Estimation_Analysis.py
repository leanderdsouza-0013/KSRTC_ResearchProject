import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
import plotly.express as px 

df = pd.read_csv(r"data/District_Info.csv")
print(df.head())

print(df.info())

print(df.describe())

print(df.isnull().sum())

print(df.duplicated().sum())

print(df.describe(include='all'))

print(df['Region_Type'].value_counts())

print(df['Interstate_Flag'].value_counts())

karnataka_df = df[df['State'] == 'Karnataka']
print(karnataka_df)

top_urban_df = df[df['Region_Type'] == 'Top Urban']
print(top_urban_df)

print(df.groupby('State')['Population_Millions'].sum())

print(df.groupby('Region_Type').size())

fig = px.bar(df, x='District', y='Population_Millions', color='State')
fig.show()