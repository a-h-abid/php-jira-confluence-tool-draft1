# PHP Jira Confluence Tool

A PHP CLI tool for fetching Jira issue content and comments from Jira Cloud and saving them locally as Markdown files. Built with Symfony Console, it supports single-issue fetching as well as bulk processing from CSV files.

## Requirements

- PHP 8.1 or higher
- [Composer](https://getcomposer.org/)
- A Jira Cloud instance with API access

## Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/a-h-abid/php-jira-confluence-tool-draft1.git
   cd php-jira-confluence-tool-draft1
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Configure credentials**

   Copy the example configuration file and fill in your Jira credentials:

   ```bash
   cp config.example.php config.php
   ```

   The application also continues to support `.env` files if you prefer:

   ```bash
   cp .env.example .env
   ```

   Edit `config.php` or `.env` with your details (see [Configuration](#configuration) below).

## Configuration

The `config.php` (or `.env`) file contains the following settings:

| Variable | Description |
| --- | --- |
| `JIRA_URL` | Base URL of your Jira Cloud instance (e.g. `https://your-org.atlassian.net`) |
| `JIRA_USERNAME` | Email address associated with your Jira account |
| `JIRA_API_TOKEN` | API token generated from [Atlassian API tokens](https://id.atlassian.com/manage-profile/security/api-tokens) |
| `SOLUTIONS_URL_LINK_PATTERN` | Regex pattern used to extract solution links from issue content |

Example `.env` file:

```dotenv
JIRA_URL=https://your-jira-instance.atlassian.net
JIRA_USERNAME=your@email.com
JIRA_API_TOKEN=your-token

SOLUTIONS_URL_LINK_PATTERN=/https://abc\.example\.com\/solutions\S+/
```

## Usage

Run commands through the `run` script:

```bash
./run <command> [options] [arguments]
```

### Available Commands

#### Fetch and save content for a single Jira issue

```bash
./run jira:fetch-and-save-content <issue-key>
```

Fetches the description/content of a Jira issue and saves it as a Markdown file under `files/stories/`.

**Example:**

```bash
./run jira:fetch-and-save-content PROJ-123
```

#### Fetch and save comments for a single Jira issue

```bash
./run jira:fetch-and-save-comments <issue-key>
```

Fetches all comments on a Jira issue and saves them as a Markdown file under `files/stories/`.

**Example:**

```bash
./run jira:fetch-and-save-comments PROJ-123
```

#### Bulk fetch contents from a CSV file

```bash
./run jira:read-from-csv-and-save-contents [--force]
```

Reads Jira issue keys from `files/input/jira-stories.csv` and fetches the content for each issue. Previously fetched issues are skipped unless the `--force` flag is used.

#### Bulk fetch comments from a CSV file

```bash
./run jira:read-from-csv-and-save-comments [--force]
```

Reads Jira issue keys from `files/input/jira-stories.csv` and fetches comments for each issue. Previously fetched issues are skipped unless the `--force` flag is used.

### CSV File Format

Place a CSV file at `files/input/jira-stories.csv` with the issue keys in the first column:

```csv
Issue key
PROJ-101
PROJ-102
PROJ-103
```

## Project Structure

```
├── files/
│   ├── input/       # Input CSV files
│   ├── output/      # Output files
│   └── stories/     # Saved Markdown files for issues
├── logs/            # Application log files
├── src/
│   ├── Actions/     # Action classes for fetching and saving data
│   ├── Config/      # Configuration (file paths)
│   ├── Console/     # Symfony Console command definitions
│   ├── DTO/         # Data Transfer Objects
│   ├── Exceptions/  # Custom exception classes
│   ├── Services/    # Jira API service layer
│   └── Utils/       # Utility classes (CSV reader, logger)
├── tmp/             # Temporary files
├── .env.example     # Example environment configuration
├── composer.json    # PHP dependencies
└── run              # CLI entry point
```

## License

See the repository for license details.
