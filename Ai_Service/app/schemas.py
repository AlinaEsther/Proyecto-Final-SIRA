from pydantic import BaseModel

class StudentRequest(BaseModel):
    student_id: int
    top_k: int = 5
