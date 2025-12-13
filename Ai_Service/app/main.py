from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional
import uvicorn
import logging

# Configurar logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="SIRA - AI Recommendation Service")


class ActivityIn(BaseModel):
    activity_id: Optional[int] = None  # Hacer opcional
    title: str
    score: Optional[float] = None
    max_points: Optional[float] = None
    percentage: Optional[float] = None


class CourseIn(BaseModel):
    course_id: Optional[int] = None
    course_name: str
    section_id: Optional[int] = None  # AGREGADO: Para identificar la sección
    average_grade: Optional[float] = None
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

    logger.info(f"📚 Generando recomendaciones para: {payload.student_name} (ID: {payload.student_id})")
    logger.info(f"📊 Número de cursos con bajo rendimiento: {len(payload.courses)}")

    recommendations = []

    # Base de recursos recomendados por materia (con múltiples opciones)
    subject_book_map = {
        "Matemáticas": [
            {
                "title": "Khan Academy - Calculus",
                "author": "Khan Academy",
                "url": "https://www.khanacademy.org/math/calculus-1"
            },
            {
                "title": "Calculus Early Transcendentals",
                "author": "James Stewart",
                "url": "https://www.amazon.com/Calculus-Early-Transcendentals-James-Stewart/dp/1285741552"
            },
            {
                "title": "MIT OpenCourseWare - Calculus",
                "author": "MIT",
                "url": "https://ocw.mit.edu/courses/mathematics/18-01-single-variable-calculus-fall-2006/"
            }
        ],
        "Matematica": [
            {
                "title": "Khan Academy - Calculus",
                "author": "Khan Academy",
                "url": "https://www.khanacademy.org/math/calculus-1"
            },
            {
                "title": "Calculus Early Transcendentals",
                "author": "James Stewart",
                "url": "https://www.amazon.com/Calculus-Early-Transcendentals-James-Stewart/dp/1285741552"
            }
        ],
        "Programación": [
            {
                "title": "The Odin Project - Full Stack",
                "author": "The Odin Project",
                "url": "https://www.theodinproject.com/"
            },
            {
                "title": "Clean Code",
                "author": "Robert C. Martin",
                "url": "https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882"
            },
            {
                "title": "freeCodeCamp",
                "author": "freeCodeCamp.org",
                "url": "https://www.freecodecamp.org/"
            }
        ],
        "Programación I": [
            {
                "title": "Python for Everybody",
                "author": "Dr. Charles Severance",
                "url": "https://www.py4e.com/"
            },
            {
                "title": "Automate the Boring Stuff with Python",
                "author": "Al Sweigart",
                "url": "https://automatetheboringstuff.com/"
            }
        ],
        "Estructura de Datos": [
            {
                "title": "Visualgo - Visualización de Estructuras",
                "author": "VisuAlgo",
                "url": "https://visualgo.net/"
            },
            {
                "title": "Data Structures and Algorithms in Python",
                "author": "Goodrich, Tamassia, Goldwasser",
                "url": "https://www.amazon.com/Structures-Algorithms-Python-Michael-Goodrich/dp/1118290275"
            }
        ],
        "Desarrollo Web": [
            {
                "title": "MDN Web Docs - JavaScript",
                "author": "Mozilla",
                "url": "https://developer.mozilla.org/es/docs/Web/JavaScript"
            },
            {
                "title": "Eloquent JavaScript",
                "author": "Marijn Haverbeke",
                "url": "https://eloquentjavascript.net/"
            },
            {
                "title": "JavaScript.info - Tutorial Moderno",
                "author": "javascript.info",
                "url": "https://javascript.info/"
            }
        ],
        "Base de Datos": [
            {
                "title": "SQL Tutorial - W3Schools",
                "author": "W3Schools",
                "url": "https://www.w3schools.com/sql/"
            },
            {
                "title": "Database System Concepts",
                "author": "Silberschatz, Korth, Sudarshan",
                "url": "https://www.amazon.com/Database-System-Concepts-Abraham-Silberschatz/dp/0078022150"
            },
            {
                "title": "PostgreSQL Tutorial",
                "author": "PostgreSQL Tutorial",
                "url": "https://www.postgresqltutorial.com/"
            }
        ],
        "Algoritmos": [
            {
                "title": "Introduction to Algorithms",
                "author": "Cormen, Leiserson, Rivest, Stein",
                "url": "https://www.amazon.com/Introduction-Algorithms-3rd-MIT-Press/dp/0262033844"
            },
            {
                "title": "LeetCode - Práctica de Algoritmos",
                "author": "LeetCode",
                "url": "https://leetcode.com/"
            }
        ],
        "Estadística": [
            {
                "title": "Khan Academy - Statistics",
                "author": "Khan Academy",
                "url": "https://www.khanacademy.org/math/statistics-probability"
            },
            {
                "title": "Statistics",
                "author": "Freedman, Pisani, Purves",
                "url": "https://www.amazon.com/Statistics-4th-David-Freedman/dp/0393929728"
            }
        ],
        "Física": [
            {
                "title": "Khan Academy - Physics",
                "author": "Khan Academy",
                "url": "https://www.khanacademy.org/science/physics"
            },
            {
                "title": "Fundamentals of Physics",
                "author": "Halliday, Resnick, Walker",
                "url": "https://www.amazon.com/Fundamentals-Physics-David-Halliday/dp/1118230728"
            }
        ],
        "Inglés": [
            {
                "title": "Duolingo - English Course",
                "author": "Duolingo",
                "url": "https://www.duolingo.com/"
            },
            {
                "title": "English Grammar in Use",
                "author": "Raymond Murphy",
                "url": "https://www.amazon.com/English-Grammar-Use-Self-Study-Intermediate/dp/1108457657"
            }
        ],
    }

    # función para obtener libro por nombre de curso (busca keywords y selecciona aleatoriamente)
    def suggest_book(course_name: str):
        import random

        for k, v in subject_book_map.items():
            if k.lower() in course_name.lower():
                # Si hay múltiples opciones, seleccionar aleatoriamente
                if isinstance(v, list):
                    return random.choice(v)
                return v

        # fallback genérico con recursos de estudio general
        fallback_resources = [
            {
                "title": "Coursera - Aprendiendo a Aprender",
                "author": "Dr. Barbara Oakley",
                "url": "https://www.coursera.org/learn/learning-how-to-learn"
            },
            {
                "title": "edX - Técnicas de Estudio",
                "author": "edX",
                "url": "https://www.edx.org/"
            },
            {
                "title": "Khan Academy - Guía de Estudio",
                "author": "Khan Academy",
                "url": "https://www.khanacademy.org/"
            }
        ]
        return random.choice(fallback_resources)

    for c in payload.courses:
        book_info = suggest_book(c.course_name or "")

        # Construir razón considerando el promedio
        if c.average_grade is not None:
            reason = (
                f"El promedio del estudiante en {c.course_name} es {c.average_grade:.1f}%, "
                f"lo cual indica necesidad de refuerzo. Se detectaron {len(c.activities)} "
                f"actividades con rendimiento inferior al 80%."
            )
        else:
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
            "course_name": c.course_name or "Asignatura desconocida",  # Agregado para compatibilidad
            "book_title": book_info["title"],
            "book_author": book_info["author"],
            "book_url": book_info.get("url", "#"),  # URL del libro
            "reason": reason,
            "activities": activities
        })

        logger.info(f"✅ Recomendación generada: {c.course_name} → {book_info['title']} por {book_info['author']}")

    logger.info(f"🎯 Total de recomendaciones generadas: {len(recommendations)}")

    return {
        "student_id": payload.student_id,
        "recommendations": recommendations
    }


@app.get("/")
def root():
    """Endpoint de bienvenida."""
    return {
        "service": "SIRA - AI Recommendation Service",
        "version": "1.0.0",
        "status": "running",
        "endpoint": "/recommend"
    }


@app.get("/health")
def health():
    """Health check endpoint."""
    return {"status": "healthy"}


# Si lo arrancas con python -m uvicorn app.main:app --reload desde la carpeta app
if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
