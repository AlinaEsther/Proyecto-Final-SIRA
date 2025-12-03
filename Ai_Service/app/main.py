from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional
import uvicorn

app = FastAPI()

class ActivityIn(BaseModel):
    activity_id: Optional[int]
    title: str
    score: Optional[float]
    max_points: Optional[float]
    percentage: Optional[float]


class CourseIn(BaseModel):
    course_id: Optional[int]
    course_name: str
    activities: List[ActivityIn]


class RecommendRequest(BaseModel):
    student_id: int
    student_name: Optional[str] = None
    courses: List[CourseIn]


@app.post("/recommend")
def recommend(payload: RecommendRequest):
    """
    Recibe una estructura con cursos y actividades donde el estudiante tiene bajo rendimiento.
    Devuelve recomendaciones (libros) por curso.
    """

    recommendations = []

    # Reglas simples/heurísticas para mapear curso -> libro (puedes expandir)
    subject_book_map = {
        "Matemáticas": "Calculus – James Stewart",
        "Matematica": "Calculus – James Stewart",
        "Programación": "Clean Code – Robert C. Martin",
        "Desarrollo Web": "Eloquent JavaScript – Marijn Haverbeke",
        "Base de Datos": "Database System Concepts – Silberschatz",
        "Algoritmos": "Introduction to Algorithms – Cormen, Leiserson, Rivest, Stein",
        "Estadística": "Statistics – Freedman, Pisani, Purves",
        "Física": "Fundamentals of Physics – Halliday & Resnick",
        "Inglés": "English Grammar in Use – Raymond Murphy",
    }

    # función para obtener libro por nombre de curso (busca keywords)
    def suggest_book(course_name: str):
        for k, v in subject_book_map.items():
            if k.lower() in course_name.lower():
                return v
        # fallback genérico
        return "Hábitos de estudio – Charles Duhigg"

    for c in payload.courses:
        book = suggest_book(c.course_name or "")
        # construir razón corta
        reason = f"Actividades con bajo rendimiento en {c.course_name} detectadas."

        # incluir actividades tal cual llegaron
        activities = []
        for a in c.activities:
            activities.append({
                "title": a.title,
                "score": f"{a.score:.2f}" if a.score is not None else None,
                "percentage": a.percentage
            })

        recommendations.append({
            "course": c.course_name or "Asignatura desconocida",
            "book": book,
            "reason": reason,
            "activities": activities
        })

    return {
        "student_id": payload.student_id,
        "recommendations": recommendations
    }


# Si lo arrancas con python -m uvicorn app.main:app --reload desde la carpeta app
if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
