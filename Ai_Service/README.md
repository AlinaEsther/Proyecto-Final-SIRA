1- Tienes que tener instalado Python en tu pc.

2- Usa: "cd: Ai_Service" en la terminal.

3-Ejecuta pip install -r requirements.txt (Si un requerimiento en especifico no se instala, revisen en requirements.txt y instalen los que les falten con el comando "pip install *nombre del requerimiento*")

4-ejecuta uvicorn main:app --reload --host 0.0.0.0 --port 8001


