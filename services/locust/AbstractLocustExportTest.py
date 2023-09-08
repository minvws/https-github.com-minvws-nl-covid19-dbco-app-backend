import random

from locust import HttpUser, task, between, constant_throughput
from faker import Faker
import logging
from PortalApiUser import PortalApiUser

fake = Faker("nl_NL")


class AbstractLocustExportTest(PortalApiUser):
    wait_time = constant_throughput(1)
    items = []
    entity = None

    def stream(self):
        datetime = fake.date_between("-2y").strftime("%Y-%m-%dT%H:%M:%SZ")
        url = "/api/export/" + self.entity + "/?since=" + datetime

        if self.debug_mode_active():
            logging.info("Streaming " + self.entity + " since " + datetime)

        return self.request_url(url)

    def get_all_entities(self):
        cursor = self.get_cursor()
        self.items = []

        while len(self.items) <= 10000:
            url = "/api/export/" + self.entity + "/?cursor=" + cursor
            response = self.request_url(url)
            json_data = response.json()

            if not json_data['items']:
                break

            self.items.extend(json_data['items'])
            cursor = json_data['cursor']

        if self.debug_mode_active():
            logging.info('total items count: ' + str(len(self.items)))

    def get_cursor(self):
        datetime = fake.date_between("-11y", '-1y').strftime("%Y-%m-%dT%H:%M:%SZ")
        url = "/api/export/" + self.entity + "/?since=" + datetime
        response = self.request_url(url)

        if response.status_code != 200:
            logging.error("Response code: " + str(response.status_code))
            return

        json_data = response.json()

        if json_data['items']:
            self.items.extend(json_data['items'])

        return json_data['cursor']

    def request_url(self, url):
        if self.debug_mode_active():
            logging.info("Requesting " + url)
            logging.info("Using header: " + str(self.use_header()))

        if self.use_header():
            self.add_login_header()
            return self.client.get(url)

        return self.client.get(url, verify=True, cert=(self.cert_file_name, self.cert_key_file_name))
