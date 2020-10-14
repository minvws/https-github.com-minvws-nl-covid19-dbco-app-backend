<?php
namespace App\Application\Repositories;

use App\Application\Models\AnswerOption;
use App\Application\Models\ClassificationDetailsQuestion;
use App\Application\Models\ContactDetailsQuestion;
use App\Application\Models\DateQuestion;
use App\Application\Models\MultipleChoiceQuestion;
use App\Application\Models\OpenQuestion;
use App\Application\Models\Question;
use App\Application\Models\Questionnaire;
use App\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
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

        $question2 = new DateQuestion();
        $question2->uuid = "37d818ed-9499-4b9a-9771-725467368388";
        $question2->group = "classification";
        $question2->label = "Wanneer was de laatste ontmoeting?";
        $question2->description = null;
        $question2->relevantForCategories = Question::ALL_CATEGORIES;
        $questionnaire->questions[] = $question2;

        $question3 = new ContactDetailsQuestion();
        $question3->uuid = "37d818ed-9499-4b9a-9770-725467368388";
        $question3->group = "contactdetails";
        $question3->label = "Contactgegevens";
        $question3->description = null;
        $question3->relevantForCategories = Question::ALL_CATEGORIES;;
        $questionnaire->questions[] = $question3;

        $question4 = new OpenQuestion();
        $question4->uuid = "37d818ed-9499-4b9a-9771-725467368389";
        $question4->group = "contactdetails";
        $question4->label = "Beroep";
        $question4->description = null;
        $question4->relevantForCategories = ["1"];
        $questionnaire->questions[] = $question4;

        $question5 = new MultipleChoiceQuestion();
        $question5->uuid = "37d818ed-9499-4b9a-9771-725467368391";
        $question5->group = "contactdetails";
        $question5->label = "Waar ken je deze persoon van?";
        $question5->description = null;
        $question5->relevantForCategories = [Question::CATEGORY_2A, Question::CATEGORY_2B];
        $question5->answerOptions[] = new AnswerOption('Ouder', 'Ouder');
        $question5->answerOptions[] = new AnswerOption('Kind', 'Kind');
        $question5->answerOptions[] = new AnswerOption('Broer of zus', 'Broer of zus');
        $question5->answerOptions[] = new AnswerOption('Partner', 'Partner');
        $question5->answerOptions[] = new AnswerOption('Familielid (overig)', 'Familielid (overig)');
        $question5->answerOptions[] = new AnswerOption('Huisgenoot', 'Huisgenoot');
        $question5->answerOptions[] = new AnswerOption('Vriend of kennis', 'Vriend of kennis');
        $question5->answerOptions[] = new AnswerOption('Medestudent of leerling', 'Medestudent of leerling');
        $question5->answerOptions[] = new AnswerOption('Collega', 'Collega');
        $question5->answerOptions[] = new AnswerOption('Gezondheidszorg medewerker', 'Gezondheidszorg medewerker');
        $question5->answerOptions[] = new AnswerOption('Ex-partner', 'Ex-partner');
        $question5->answerOptions[] = new AnswerOption('Overig', 'Overig');
        $questionnaire->questions[] = $question5;

        $question6 = new MultipleChoiceQuestion();
        $question6->uuid = "37d818ed-9499-4b9a-9771-725467368391";
        $question6->group = "contactdetails";
        $question6->label = "Is een of meerdere onderstaande zaken van toepassing voor deze persoon?";
        $question6->description =
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
        $question6->relevantForCategories = [Question::CATEGORY_1, Question::CATEGORY_2A, Question::CATEGORY_2B];
        $question6->answerOptions[] = new AnswerOption('Ja, één of meerdere dingen', 'Ja', 'communication_staff');
        $question6->answerOptions[] = new AnswerOption('Nee, ik denk het niet', 'Nee', 'communication_index');
        $questionnaire->questions[] = $question6;

        $list = new QuestionnaireList();
        $list->questionnaires[] = $questionnaire;

        return $list;
    }
}
