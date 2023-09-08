from locust import HttpUser, task, between
from os import environ

cert_file_name = environ["LOCUST_CERT_FILE_NAME"]
cert_key_file_name = environ["LOCUST_CERT_KEY_FILE_NAME"]
portal_host = environ["PORTAL_HOST"]
use_header = environ["USE_HEADER"]


class PortalApiUser(HttpUser):
    abstract = True
    host = portal_host

    def add_login_header(self):
        self.client.verify = False
        self.client.headers = {
            'SSL-Client-Subject-DN': 'CN=' + environ['CN_NAME'],
            'Accept': 'application/json',
            'Host': 'localhost'
        }

    def use_header(self):
        return environ['USE_HEADER'] == 'true'

    def debug_mode_active(self):
        return environ['DEBUG_MODE'] == 'true'
