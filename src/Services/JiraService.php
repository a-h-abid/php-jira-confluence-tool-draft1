<?php

namespace AHAbid\JiraItTest\Services;

use AHAbid\JiraItTest\DTO\JiraComment;
use AHAbid\JiraItTest\Exceptions\CurlResponseException;
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;
use CurlHandle;

class JiraService
{
    private CurlHandle|false $curlHandle;
    private HtmlConverter $markdownConverter;

    public function __construct(
        public readonly string $instanceUrl,
        public readonly string $username,
        public readonly string $token
    ) {
        $this->initCurlHandle();
        $this->markdownConverter = new HtmlConverter();
        $this->markdownConverter->getEnvironment()->addConverter(new TableConverter());
    }

    public function __destruct()
    {
        $this->closeCurlHandle();
    }

    public static function build(): self
    {
        return new self(
            env('JIRA_URL'),
            env('JIRA_USERNAME'),
            env('JIRA_API_TOKEN')
        );
    }

    public function getContent($jiraIssueId, $convertToMarkdown = false): string
    {
        $content = $this->sendRequestToJira($this->instanceUrl . '/rest/api/3/issue/' . $jiraIssueId . '?fields=description&expand=renderedFields', 'GET');
        $html = $content['renderedFields']['description'] ?? '';

        if (!$convertToMarkdown) {
            return $html;
        }

        return $this->markdownConverter->convert($html);
    }

    /**
     * @return JiraComment[]
     */
    public function getComments($jiraIssueId): array
    {
        $comments = $this->sendRequestToJira($this->instanceUrl . '/rest/api/3/issue/' . $jiraIssueId . '/comment?expand=renderedBody', 'GET');

        $comments = array_map(function ($comment) {
            return new JiraComment(
                createdAt: \DateTime::createFromFormat('Y-m-d\TH:i:s.vO', $comment['created']),
                body: $this->markdownConverter->convert($comment['renderedBody']),
                authorName: $comment['author']['displayName'],
                authorEmail: $comment['author']['emailAddress'] ?? null,
            );
        }, $comments['comments'] ?? []);

        return $comments;
    }

    private function initCurlHandle(): void
    {
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
        curl_setopt($this->curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->token);
    }

    /**
     * @throws CurlResponseException
     */
    private function sendRequestToJira(string $url, string $method = 'GET', string|array $data = ''): array
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $data);

        writeLog('Sending Request to Jira: ' . $url);

        $response = curl_exec($this->curlHandle);
        $httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        writeLog('Received Response from Jira: ' . $httpCode);

        if ($httpCode != 200) {
            throw new CurlResponseException("Error JIRA Response {$httpCode}: {$response}");
        }

        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            throw new CurlResponseException("Error Jira Response is not in JSON | {$jsonError} | Resp: {$response}");
        }

        return $json;
    }

    private function closeCurlHandle(): void
    {
        if ($this->curlHandle) {
            curl_close($this->curlHandle);
        }

        $this->curlHandle = false;
    }
}
