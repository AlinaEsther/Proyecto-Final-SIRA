import redis
import json

class RedisCache:
    def __init__(self):
        self.client = redis.Redis(host="localhost", port=6379, decode_responses=True)

    def get(self, key):
        data = self.client.get(key)
        return json.loads(data) if data else None

    def set(self, key, value):
        self.client.set(key, json.dumps(value), ex=3600)
