<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Marufsharia\Hyro\Facades\Hyro;
use Illuminate\Support\Facades\Route;

class Header extends Component
{
    public $search = '';
    public $searchResults = [];
    public $showResults = false;
    public $selectedIndex = -1;
    public $isSearching = false;

    protected $listeners = ['avatar-updated' => '$refresh'];

    public function mount()
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showResults = false;
        $this->selectedIndex = -1;
    }

    public function updatedSearch($value)
    {
        $this->selectedIndex = -1;
        
        if (strlen($value) < 1) {
            $this->searchResults = [];
            $this->showResults = false;
            $this->isSearching = false;
            return;
        }

        $this->isSearching = true;
        $this->searchResults = $this->performFuzzySearch($value);
        $this->showResults = true;
        $this->isSearching = false;
    }

    protected function performFuzzySearch($query)
    {
        $results = [];
        $query = strtolower(trim($query));

        // Define all searchable items
        $searchableItems = [
            // System
            ['title' => 'Dashboard', 'url' => route('hyro.admin.dashboard'), 'group' => 'System', 'icon' => 'home', 'keywords' => ['dashboard', 'home', 'main', 'overview'], 'description' => 'View system overview'],
            ['title' => 'Plugins', 'url' => route('hyro.admin.plugins.index'), 'group' => 'System', 'icon' => 'puzzle', 'keywords' => ['plugin', 'plugins', 'manager', 'extensions', 'addons'], 'description' => 'Manage plugins'],
            ['title' => 'Settings', 'url' => route('hyro.admin.settings.index'), 'group' => 'System', 'icon' => 'cog', 'keywords' => ['settings', 'config', 'configuration', 'preferences', 'options'], 'description' => 'System settings'],
            
            // Management
            ['title' => 'Roles', 'url' => route('hyro.admin.roles.index'), 'group' => 'Management', 'icon' => 'users', 'keywords' => ['roles', 'permissions', 'access', 'security', 'authorization'], 'description' => 'Manage user roles'],
            ['title' => 'Privileges', 'url' => route('hyro.admin.privileges.index'), 'group' => 'Management', 'icon' => 'shield', 'keywords' => ['privileges', 'permissions', 'access', 'rights'], 'description' => 'Manage privileges'],
            
            // Profile
            ['title' => 'My Profile', 'url' => route('profile.index'), 'group' => 'Account', 'icon' => 'user', 'keywords' => ['profile', 'account', 'user', 'me', 'personal'], 'description' => 'View and edit profile'],
        ];

        // Add sidebar items from plugins/modules
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
                                'keywords' => [strtolower($item['title']), strtolower($sectionOrItem['group'])],
                                'description' => $sectionOrItem['group'] . ' feature'
                            ];
                        }
                    }
                } elseif (isset($sectionOrItem['title'])) {
                    // Single item
                    $url = $sectionOrItem['url'] ?? '#';
                    if (isset($sectionOrItem['route']) && Route::has($sectionOrItem['route'])) {
                        $url = route($sectionOrItem['route']);
                    }
                    
                    $searchableItems[] = [
                        'title' => $sectionOrItem['title'],
                        'url' => $url,
                        'group' => 'Features',
                        'icon' => 'puzzle',
                        'keywords' => [strtolower($sectionOrItem['title'])],
                        'description' => 'Feature'
                    ];
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

        return array_slice($results, 0, 10);
    }

    protected function calculateFuzzyScore($query, $item)
    {
        $score = 0;
        $title = strtolower($item['title']);
        $group = strtolower($item['group'] ?? '');
        
        // Exact match - highest priority
        if ($title === $query) {
            return 10000;
        }
        
        // Starts with query - very high priority
        if (str_starts_with($title, $query)) {
            $score += 5000;
        }
        
        // Contains query as whole word
        if (str_contains($title, ' ' . $query . ' ') || str_contains($title, ' ' . $query) || str_contains($title, $query . ' ')) {
            $score += 3000;
        }
        
        // Contains query anywhere
        if (str_contains($title, $query)) {
            $score += 2000;
        }
        
        // Check group
        if (str_contains($group, $query)) {
            $score += 500;
        }
        
        // Check keywords
        foreach ($item['keywords'] ?? [] as $keyword) {
            if ($keyword === $query) {
                $score += 1500;
            } elseif (str_starts_with($keyword, $query)) {
                $score += 1000;
            } elseif (str_contains($keyword, $query)) {
                $score += 500;
            }
        }
        
        // Fuzzy character matching (sequential)
        $queryChars = str_split($query);
        $titleChars = str_split($title);
        $matchCount = 0;
        $titleIndex = 0;
        $sequential = true;
        
        foreach ($queryChars as $char) {
            $found = false;
            for ($i = $titleIndex; $i < count($titleChars); $i++) {
                if ($titleChars[$i] === $char) {
                    $matchCount++;
                    $titleIndex = $i + 1;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sequential = false;
            }
        }
        
        if ($matchCount === count($queryChars) && $sequential) {
            $score += 100 + (200 * ($matchCount / max(strlen($title), 1)));
        } elseif ($matchCount > 0) {
            $score += 50 * ($matchCount / count($queryChars));
        }
        
        return $score;
    }

    public function selectNext()
    {
        if (count($this->searchResults) > 0) {
            $this->selectedIndex = min($this->selectedIndex + 1, count($this->searchResults) - 1);
        }
    }

    public function selectPrevious()
    {
        $this->selectedIndex = max($this->selectedIndex - 1, -1);
    }

    public function selectCurrent()
    {
        if ($this->selectedIndex >= 0 && isset($this->searchResults[$this->selectedIndex])) {
            return $this->searchResults[$this->selectedIndex]['url'];
        }
        return null;
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->searchResults = [];
        $this->showResults = false;
        $this->selectedIndex = -1;
    }

    public function render()
    {
        return view('hyro::admin.layouts.partials.header');
    }
}
