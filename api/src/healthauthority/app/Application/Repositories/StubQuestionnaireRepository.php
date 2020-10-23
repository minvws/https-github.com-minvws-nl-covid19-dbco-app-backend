<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\AnswerOption;
use DBCO\HealthAuthorityAPI\Application\Models\ClassificationDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\ContactDetailsQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\DateQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\MultipleChoiceQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\OpenQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\Question;
use DBCO\HealthAuthorityAPI\Application\Models\Questionnaire;
use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * Stub implementation.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
 */
class StubQuestionnaireRepository implements QuestionnaireRepository
{
    /**
     * Returns the questionnaire list.
     *
     * @return QuestionnaireList
     */
    public function getQuestionnaires(): QuestionnaireList
    {
        $questionnaire = new Questionnaire();
        $questionnaire->uuid = "3fa85f64-5717-4562-b3fc-2c963f66afa6";
        $questionnaire->taskType = "contact";

        $question1 = new ClassificationDetailsQuestion();
        $question1->uuid = "37d818ed-9499-4b9a-9771-725467368387";
        $question1->group = "classification";
        $question1->label = "Vragen over jullie ontmoeting";
        $question1->description = null;
        $question1->relevantForCategories = Question::ALL_CATEGORIES;
        $questionnaire->questions[] = $question1;

        $question2 = new ContactDetailsQuestion();
        $question2->uuid = "37d818ed-9499-4b9a-9770-725467368388";
        $question2->group = "contactdetails";
        $question2->label = "Contactgegevens";
        $question2->description = null;
        $question2->relevantForCategories = Question::ALL_CATEGORIES;;
        $questionnaire->questions[] = $question2;

        $question3 = new OpenQuestion();
        $question3->uuid = "37d818ed-9499-4b9a-9771-725467368389";
        $question3->group = "contactdetails";
        $question3->label = "Beroep";
        $question3->description = null;
        $question3->relevantForCategories = ["1"];
        $questionnaire->questions[] = $question3;

        $question4 = new MultipleChoiceQuestion();
        $question4->uuid = "37d818ed-9499-4b9a-9771-725467368390";
        $question4->group = "contactdetails";
        $question4->label = "Waar ken je deze persoon van?";
        $question4->description = null;
        $question4->relevantForCategories = [Question::CATEGORY_2A, Question::CATEGORY_2B];
        $question4->answerOptions[] = new AnswerOption('Ouder', 'Ouder');
        $question4->answerOptions[] = new AnswerOption('Kind', 'Kind');
        $question4->answerOptions[] = new AnswerOption('Broer of zus', 'Broer of zus');
        $question4->answerOptions[] = new AnswerOption('Partner', 'Partner');
        $question4->answerOptions[] = new AnswerOption('Familielid (overig)', 'Familielid (overig)');
        $question4->answerOptions[] = new AnswerOption('Huisgenoot', 'Huisgenoot');
        $question4->answerOptions[] = new AnswerOption('Vriend of kennis', 'Vriend of kennis');
        $question4->answerOptions[] = new AnswerOption('Medestudent of leerling', 'Medestudent of leerling');
        $question4->answerOptions[] = new AnswerOption('Collega', 'Collega');
        $question4->answerOptions[] = new AnswerOption('Gezondheidszorg medewerker', 'Gezondheidszorg medewerker');
        $question4->answerOptions[] = new AnswerOption('Ex-partner', 'Ex-partner');
        $question4->answerOptions[] = new AnswerOption('Overig', 'Overig');
        $questionnaire->questions[] = $question4;

        $question5 = new MultipleChoiceQuestion();
        $question5->uuid = "37d818ed-9499-4b9a-9771-725467368391";
        $question5->group = "contactdetails";
        $question5->label = "Is een of meerdere onderstaande zaken van toepassing voor deze persoon?";
        $question5->description =
            implode(
                "\n",
                [
                    "* Is student",
                    "* 70 jaar of ouder",
                    "* Heeft gezondheidsklachten of loopt extra gezondheidsrisico's",
                    "* Woont in een asielzoekerscentrum",
                    "* Spreekt slecht of geen Nederlands"
                ]
            );
        $question5->relevantForCategories = [Question::CATEGORY_1, Question::CATEGORY_2A, Question::CATEGORY_2B];
        $question5->answerOptions[] = new AnswerOption('Ja, één of meerdere dingen', 'Ja', 'communication_staff');
        $question5->answerOptions[] = new AnswerOption('Nee, ik denk het niet', 'Nee', 'communication_index');
        $questionnaire->questions[] = $question5;

        $list = new QuestionnaireList();
        $list->questionnaires[] = $questionnaire;

        return $list;
    }
}
