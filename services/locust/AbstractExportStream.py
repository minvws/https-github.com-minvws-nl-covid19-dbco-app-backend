from AbstractLocustExportTest import AbstractLocustExportTest
from locust import task


class AbstractExportStream(AbstractLocustExportTest):

    @task()
    def stream_task(self):
        self.stream()
