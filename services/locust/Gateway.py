import json, jwt, os, csv, itertools
from locust import HttpUser, task, constant_throughput
from faker import Faker
from pathlib import Path

jwt_secret = os.environ['GATEWAY_JWT_SECRET']
ggd_identifier_iteration_enabled = os.getenv('GGD_IDENTIFIER_ITERATION_ENABLED', 'true')
fake = Faker('nl_NL')

def payload(ggd_identifier):
    payload = {
        "orderId":str(fake.bothify(letters="ABCDE", text="###?######")),
        "messageId":str(fake.random_int(100000000, 999999999)),
        "senderRegion":None,
        "hpzoneNumber":fake.uuid4(),
        "ggdIdentifier":ggd_identifier,
        "person":{
            "initials":fake.random_uppercase_letter() + ".",
            "firstName":fake.first_name(),
            "surname":fake.last_name(),
            "bsn":fake.random_element(elements=(None, fake.ssn())),
            "vNumber":None,
            "dateOfBirth":fake.date_of_birth().strftime("%m-%d-%Y"),
            "gender":fake.random_element(elements=("MAN", "VROUW")),
            "email":fake.ascii_safe_email(),
            "mobileNumber":None,
            "telephoneNumber":fake.phone_number(),
            "address":{
                "streetName":fake.street_name(),
                "houseNumber":fake.building_number(),
                "houseNumberSuffix":None,
                "postcode":fake.postcode(),
                "city":fake.city()
            },
            "huisartsNaam":None,
            "huisartsPlaats":None
        },
        "triage":{
            "dateOfFirstSymptom":fake.date_between("-1w").strftime("%m-%d-%Y"),
            "symptomsNote":None,
            "temperature":None,
            "healthNote":None
        },
        "workLocation":{
            "organisation":fake.company(),
            "nameOfTheLocation":None,
            "department":None,
            "position":None,
            "address":{
                "streetName":None,
                "houseNumber":None,
                "houseNumberSuffix":None,
                "postcode":None,
                "city":None
            },
            "otherKnownCases":[None],
            "employmentType":[None]
        },
        "test":{
            "sampleDate":fake.date_time_between("-1w").strftime("%Y-%m-%dT%H:%M:%S%z"),
            "resultDate":fake.date_time_between("-1w").strftime("%Y-%m-%dT%H:%M:%S.00Z"),
            "sampleLocation":fake.city(),
            "sampleId":str(fake.bothify(letters="ABCDE", text="###?#######")),
            "typeOfTest":fake.random_element(elements=('SARS-CoV-2 Zelftest', 'SARS-CoV-2 PCR', 'SARS-CoV-2 Antigeen', None)),
            "result":"POSITIEF",
            "source":"CoronIT",
            "testLocation":"GGD Apeldoorn",
            "testLocationCategory":"GGD instance"
        }
    }

    return payload

def load_ggd_identifiers():
    if (ggd_identifier_iteration_enabled != 'true'):
        return ["000000"]

    base_path = Path(__file__).parent
    file_path = (base_path / 'assets/organisations.csv').resolve()
    csv_file = csv.DictReader(file_path.open('r'))

    ggd_identifiers = []
    for column in csv_file:
        ggd_identifiers.append(column['hp_zone_code'])

    return ggd_identifiers

class Gateway(HttpUser):
    """
    The constant_throughput function gives us the opportunity to control the amount of requests being made per second.
    This can be controlled by the number of users provided in the Locust interface.
    This way we can manually ramp up the number of requests by an edit of the running load test.

    For example:
    10 users = 1 request per second
    20 users = 2 requests per second
    30 users = 3 requests per second

    ...and so on
    """
    wait_time = constant_throughput(0.1)

    ggd_identifiers = itertools.cycle(load_ggd_identifiers())

    @task(1)
    def postTestResults(self):
        ggd_identifier = next(self.ggd_identifiers)
        jwt_token = jwt.encode({'http://ggdghor.nl/payload': json.JSONEncoder().encode(payload(ggd_identifier))}, jwt_secret)
        headers = {'Authorization': 'Bearer ' + jwt_token}

        self.client.post("/api/v1/test-results", headers=headers)
