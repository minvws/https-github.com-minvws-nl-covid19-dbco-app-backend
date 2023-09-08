<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\MessageException;
use App\Helpers\Config;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use Illuminate\Support\Facades\App;
use Illuminate\View\Factory as ViewFactory;
use MinVWS\DBCO\Enum\Models\EmailLanguage;

use function array_merge;
use function htmlspecialchars;
use function nl2br;

class MessageTextRenderer
{
    public function __construct(
        private readonly ViewFactory $view,
    ) {
    }

    /**
     * @throws MessageException
     */
    public function createText(
        EmailLanguage $emailLanguage,
        string $filename,
        bool $isSecure,
        EloquentCase $eloquentCase,
        ?EloquentTask $eloquentTask,
        ?string $additionalAdvice,
        array $additionalViewData = [],
    ): string {
        // override locale when generating message
        App::setLocale($emailLanguage->value);

        $viewData = array_merge([
            'caseNumber' => $eloquentCase->case_id,
            'name' => $eloquentTask !== null ? $eloquentTask->name : $eloquentCase->name,
            'phoneNumber' => $eloquentCase->contact->phone,
            'ggdRegion' => $eloquentCase->organisation->name,
            'ggdPhoneNumber' => $eloquentCase->organisation->phone_number,
        ], $additionalViewData);

        if ($isSecure) {
            if ($additionalAdvice === null) {
                $viewData['additionalAdvice'] = null;
                $viewData['customTextPlaceholder'] = '%custom_text_placeholder%';
            } else {
                // Escape user input and then convert newlines to <br> tags
                $viewData['additionalAdvice'] = nl2br(htmlspecialchars($additionalAdvice));
                $viewData['customTextPlaceholder'] = null;
            }
        }

        $text = $this->view->make(EmailTemplateHelper::getTemplatePath($emailLanguage, $filename), $viewData)->render();

        // restore locale to the app-locale
        App::setLocale(Config::string('app.locale'));

        return $text;
    }
}
