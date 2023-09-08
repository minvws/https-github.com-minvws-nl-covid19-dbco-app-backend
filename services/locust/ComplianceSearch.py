import logging

from locust import HttpUser, task
from faker import Faker
from PortalApiUser import PortalApiUser

fake = Faker("nl_NL")

class ComplianceSearch(PortalApiUser):
    def on_start(self):
        self.client.get("/auth/stub?uuid=00000000-0000-0000-0000-000000000005")

    @task(1)
    def postSearch(self):
        json = {
            "email": "foo@bar.com",
            "lastname": "Bar",
        }

        xsrf = None
        for name, value in self.client.cookies.iteritems():
            if name == "XSRF-TOKEN":
                xsrf = value[0:-3] #the cookie value ends with an url encoded equals char, which we need to strip off

        self.client.headers.update({"X-Xsrf-Token": xsrf})

        r = self.client.post("/api/search", json=json)
