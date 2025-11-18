import pandas as pd

class DataProcessor:

    def load_student_data(self):
        df = pd.read_csv("data/sample_data.csv")
        return df

    def get_student_vector(self, df, student_id):
        row = df[df["student_id"] == student_id]
        if row.empty:
            return None
        return row.drop(columns=["student_id"]).values[0]
