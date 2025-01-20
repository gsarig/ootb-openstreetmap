const { exec } = require('child_process');
const fs = require('fs/promises');
const path = require('path');

class LocalQAGenerator {
    constructor(repoPath) {
        this.repoPath = repoPath;
    }

    async getDiff() {
        return new Promise((resolve, reject) => {
            exec('git diff HEAD', { cwd: this.repoPath }, (error, stdout, stderr) => {
                if (error) reject(error);
                resolve(stdout);
            });
        });
    }

    async generateQACriteria(diff) {
        const response = await fetch('http://localhost:11434/api/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model: 'codellama',
                prompt: `Analyze these WordPress code changes and create QA testing criteria.
                Focus on WordPress-specific testing steps based on the type of changes.
                For each change, specify the required user role (Editor/Admin/etc),
                testing steps, and expected results.

                Code changes:
                ${diff}

                Format the response as markdown with clear sections.`
            })
        });

        let fullResponse = '';
        for await (const chunk of response.body) {
            const decoded = new TextDecoder().decode(chunk);
            const data = JSON.parse(decoded);
            fullResponse += data.response;
        }

        return fullResponse;
    }

    async saveQACriteria(criteria) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filePath = path.join(this.repoPath, `qa-criteria-${timestamp}.md`);
        await fs.writeFile(filePath, criteria);
        return filePath;
    }
}

// Usage in a git hook:
async function main() {
    try {
        const generator = new LocalQAGenerator(process.cwd());
        const diff = await generator.getDiff();
        const criteria = await generator.generateQACriteria(diff);
        const filePath = await generator.saveQACriteria(criteria);
        console.log(`QA criteria saved to: ${filePath}`);
    } catch (error) {
        console.error('Error generating QA criteria:', error);
        process.exit(1);
    }
}

if (require.main === module) {
    main();
}

module.exports = LocalQAGenerator;
