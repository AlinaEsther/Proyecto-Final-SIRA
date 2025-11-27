from fastapi import FastAPI
from .schemas import StudentRequest
from .recommender import RecommendationEngine
from .data_pipeline import DataProcessor
from .cache import RedisCache

app = FastAPI(
    title="SIRA AI Service",
    description="Microservicio de recomendaciones académicas",
    version="1.0.0"
)

# Inicializar componentes
processor = DataProcessor()
cache = RedisCache()
engine = RecommendationEngine(processor, cache)


@app.get("/")
def root():
    return {"message": "SIRA AI Service Running ✓"}


@app.post("/recommend")
def recommend(request: StudentRequest):
    result = engine.recommend_resources(
        student_id=request.student_id,
        top_k=request.top_k
    )
    return result
