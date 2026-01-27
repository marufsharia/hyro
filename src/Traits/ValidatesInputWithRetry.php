<?php
namespace Marufsharia\Hyro\Traits;

trait ValidatesInputWithRetry
{
    protected function askWithRetry(string $question, array $rules, ?string $default = null, bool $secret = false): string
    {
        $attempt = 1;
        $maxAttempts = 3;

        while ($attempt <= $maxAttempts) {
            if ($attempt > 1) {
                $this->warn("Attempt {$attempt} of {$maxAttempts}");
            }

            $value = $secret
                ? $this->secret($question)
                : $this->ask($question, $default);

            $validator = validator([$question => $value], [$question => $rules]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }

                if ($attempt === $maxAttempts) {
                    throw new \RuntimeException("Maximum attempts reached for: {$question}");
                }

                $attempt++;
                continue;
            }

            return $value;
        }

        throw new \RuntimeException("Failed to get valid input for: {$question}");
    }

    protected function validateWithRetry(array $data, array $rules, array $messages = [], int $maxAttempts = 3): array
    {
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            $validator = validator($data, $rules, $messages);

            if ($validator->fails()) {
                if ($attempt === 1) {
                    $this->error('Validation failed:');
                } else {
                    $this->warn("Attempt {$attempt} of {$maxAttempts}");
                }

                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }

                if ($attempt === $maxAttempts) {
                    throw new \RuntimeException('Maximum validation attempts reached.');
                }

                // In interactive mode, give option to retry
                if (!$this->option('no-interaction') && !$this->confirm('Try again?', true)) {
                    throw new \RuntimeException('Validation cancelled by users.');
                }

                $attempt++;
                continue;
            }

            return $data;
        }

        throw new \RuntimeException('Validation failed after maximum attempts.');
    }
}
