from itertools import cycle
from locust import HttpUser, task, between, constant_throughput
import logging
from AbstractLocustExportTest import AbstractLocustExportTest


class AbstractExportObject(AbstractLocustExportTest):
    iterator = None

    def on_start(self):
        self.get_all_entities()
        self.iterator = cycle(self.items)

    @task()
    def export(self):
        if not self.items:
            if self.debug_mode_active():
                logging.info('Items was empty, not sending request for object export')
            return

        item = next(self.iterator)
        self.client.get(item['path'])
