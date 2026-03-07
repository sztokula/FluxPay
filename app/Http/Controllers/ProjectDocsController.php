<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProjectDocsController extends Controller
{
    public function documentation(): View
    {
        return $this->renderMarkdownPage(
            base_path('README.md'),
            'Documentation',
            'Project overview and implementation details.'
        );
    }

    public function changelog(): View
    {
        return $this->renderMarkdownPage(
            base_path('CHANGELOG.md'),
            'Changelog',
            'Version history and delivered improvements.'
        );
    }

    public function lessonsLearned(): View
    {
        return $this->renderMarkdownPage(
            base_path('docs/LESSONS_LEARNED.md'),
            'What I Learned',
            'Practical lessons from building and hardening this platform.'
        );
    }

    private function renderMarkdownPage(string $path, string $title, string $description): View
    {
        abort_unless(File::exists($path), 404);
        [$contentHtml, $toc] = $this->withHeadingAnchorsAndToc(Str::markdown(File::get($path)));

        return view('storefront.docs', [
            'title' => $title,
            'description' => $description,
            'contentHtml' => $contentHtml,
            'toc' => $toc,
        ]);
    }

    /**
     * @return array{0: string, 1: array<int, array{id: string, title: string, level: int}>}
     */
    private function withHeadingAnchorsAndToc(string $html): array
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $headings = $xpath->query('//h1|//h2|//h3');

        $usedIds = [];
        $toc = [];

        if ($headings !== false) {
            foreach ($headings as $heading) {
                $title = trim((string) $heading->textContent);

                if ($title === '') {
                    continue;
                }

                $baseId = Str::slug($title);
                $baseId = $baseId !== '' ? $baseId : 'section';

                $id = $baseId;
                $suffix = 2;
                while (array_key_exists($id, $usedIds)) {
                    $id = $baseId.'-'.$suffix;
                    $suffix++;
                }

                $usedIds[$id] = true;
                $heading->setAttribute('id', $id);

                $toc[] = [
                    'id' => $id,
                    'title' => $title,
                    'level' => (int) str_replace('h', '', strtolower($heading->nodeName)),
                ];
            }
        }

        $contentHtml = '';
        $body = $dom->getElementsByTagName('body')->item(0);

        if ($body !== null) {
            foreach ($body->childNodes as $childNode) {
                $contentHtml .= $dom->saveHTML($childNode);
            }
        }

        return [$contentHtml, $toc];
    }
}
