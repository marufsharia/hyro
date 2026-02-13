<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Marufsharia\Hyro\Facades\Hyro;
use Illuminate\Support\Facades\Route;

class Header extends Component
{
    public $search = '';
    public $searchResults = [];
    public $showResults = false;

    public function mount()
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showResults = false;
    }

    public function updatedSearch($value)
    {
        if (strlen($value) < 1) {
            $this->searchResults = [];
            $this->showResults = false;
            return;
        }

        $this->searchResults = $this->performFuzzySearch($value);
        $this->showResults = count($this->searchResults) > 0;
    }

    protected function performFuzzySearch($query)
    {
        $results = [];
        $query = strtolower(trim($query));

        // Define all searchable items
        $searchableItems = [
            // System
            ['title' => 'Dashboard', 'url' => route('hyro.admin.dashboard'), 'group' => 'System', 'icon' => 'home', 'keywords' => ['dashboard', 'home', 'main']],
            ['title' => 'Plugin Manager', 'url' => route('hyro.admin.plugins'), 'group' => 'System', 'icon' => 'puzzle', 'keywords' => ['plugin', 'plugins', 'manager', 'extensions']],
            
            // Management
            ['title' => 'Users', 'url' => '#', 'group' => 'Management', 'icon' => 'users', 'keywords' => ['users', 'user', 'people', 'accounts']],
            ['title' => 'Roles', 'url' => '#', 'group' => 'Management', 'icon' => 'shield', 'keywords' => ['roles', 'permissions', 'access', 'security']],
            ['title' => 'Settings', 'url' => '#', 'group' => 'System', 'icon' => 'cog', 'keywords' => ['settings', 'config', 'configuration', 'preferences']],
        ];

        // Add sidebar items
        try {
            $sidebarItems = Hyro::sidebar();
            
            foreach ($sidebarItems as $sectionOrItem) {
                if (isset($sectionOrItem['group']) && isset($sectionOrItem['items'])) {
                    foreach ($sectionOrItem['items'] as $item) {
                        if (isset($item['title'])) {
                            $url = $item['url'] ?? '#';
                            if (isset($item['route']) && Route::has($item['route'])) {
                                $url = route($item['route']);
                            }
                            
                            $searchableItems[] = [
                                'title' => $item['title'],
                                'url' => $url,
                                'group' => $sectionOrItem['group'],
                                'icon' => 'puzzle',
                                'keywords' => [strtolower($item['title'])]
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
        }

        // Fuzzy search algorithm
        foreach ($searchableItems as $item) {
            $score = $this->calculateFuzzyScore($query, $item);
            
            if ($score > 0) {
                $item['score'] = $score;
                $results[] = $item;
            }
        }

        // Sort by score (highest first)
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($results, 0, 8);
    }

    protected function calculateFuzzyScore($query, $item)
    {
        $score = 0;
        $title = strtolower($item['title']);
        
        // Exact match
        if ($title === $query) {
            return 1000;
        }
        
        // Starts with query
        if (str_starts_with($title, $query)) {
            $score += 500;
        }
        
        // Contains query
        if (str_contains($title, $query)) {
            $score += 300;
        }
        
        // Check keywords
        foreach ($item['keywords'] ?? [] as $keyword) {
            if (str_contains($keyword, $query)) {
                $score += 200;
            }
            if (str_starts_with($keyword, $query)) {
                $score += 100;
            }
        }
        
        // Fuzzy character matching
        $queryChars = str_split($query);
        $titleChars = str_split($title);
        $matchCount = 0;
        $titleIndex = 0;
        
        foreach ($queryChars as $char) {
            for ($i = $titleIndex; $i < count($titleChars); $i++) {
                if ($titleChars[$i] === $char) {
                    $matchCount++;
                    $titleIndex = $i + 1;
                    break;
                }
            }
        }
        
        if ($matchCount === count($queryChars)) {
            $score += 50 + (100 * ($matchCount / strlen($title)));
        }
        
        return $score;
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showResults = false;
    }

    public function render()
    {
        return view('hyro::admin.layouts.partials.header');
    }
}
