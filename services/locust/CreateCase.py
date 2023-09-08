import logging

from locust import HttpUser, task
from faker import Faker
from PortalApiUser import PortalApiUser

fake = Faker("nl_NL")

class CreateCase(PortalApiUser):
    def on_start(self):
        self.client.get("/auth/stub?uuid=00000000-0000-0000-0000-000000000001")

    @task(1)
    def postCase(self):
        json = {
            "index": {
                "firstname": fake.first_name(),
                "lastname": fake.last_name(),
                "dateOfBirth": fake.date()
            },
            "contact": {
                "phone": fake.phone_number()
            }
        }

        self.client.post("/api/cases/", json=json)

