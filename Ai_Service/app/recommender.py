import numpy as np
from .utils import cosine_similarity

class RecommendationEngine:

    def __init__(self, processor, cache):
        self.processor = processor
        self.cache = cache

    def recommend_resources(self, student_id: int, top_k: int = 5):

        # Revisar cache
        cache_key = f"rec_{student_id}"
        cached = self.cache.get(cache_key)
        if cached:
            return cached

        df = self.processor.load_student_data()
        student_vector = self.processor.get_student_vector(df, student_id)

        if student_vector is None:
            return {"error": "Student not found"}

        similarities = []
        for i, row in df.iterrows():
            if row["student_id"] != student_id:
                vec = row.drop(labels=["student_id"]).values
                sim = cosine_similarity(student_vector, vec)
                similarities.append((row["student_id"], sim))

        similarities.sort(key=lambda x: x[1], reverse=True)
        top = similarities[:top_k]

        response = {
            "student_id": student_id,
            "recommendations": [
                {"related_student": s, "similarity": float(score)}
                for s, score in top
            ]
        }

        self.cache.set(cache_key, response)
        return response
