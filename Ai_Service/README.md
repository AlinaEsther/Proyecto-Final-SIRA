Activación del Servicio IA (Si no se activa el servicio la parte de recomendaciones NO va a funcionar.)

1- Tienes que tener instalado Python en tu pc.

2- Usa: "cd Ai_Service" en la terminal.

3-Ejecuta python -m venv .venv

4-Ejecuta .venv\Scripts\activate

5-Ejecuta pip install -r requirements.txt (Si un requerimiento en especifico no se instala, revisa en requirements.txt y instalen los que les falten con el comando "pip install *nombre del requerimiento*")

6-ejecuta uvicorn app.main:app --reload --host 127.0.0.1 --port 8001



