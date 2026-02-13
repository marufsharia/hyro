<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Marufsharia\Hyro\Facades\Hyro;

class Header extends Component
{
    public $search = '';
    public $searchResults = [];
    public $showResults = false;

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            $this->showResults = false;
            return;
        }

        $this->searchResults = $this->performSearch($this->search);
        $this->showResults = true;
    }

    protected function performSearch($query)
    {
        $results = [];
        $query = strtolower($query);

        // Search in sidebar items
        $sidebarItems = Hyro::sidebar();
        
        foreach ($sidebarItems as $sectionOrItem) {
            if (isset($sectionOrItem['group']) && isset($sectionOrItem['items'])) {
                // Section with items
                foreach ($sectionOrItem['items'] as $item) {
                    if ($this->matchesSearch($item, $query)) {
                        $results[] = [
                            'title' => $item['title'] ?? 'Untitled',
                            'url' => $item['url'] ?? ($item['route'] ?? '#'),
                            'group' => $sectionOrItem['group'],
                            'icon' => 'puzzle',
                            'type' => 'menu'
                        ];
                    }
                }
            } elseif (isset($sectionOrItem['title'])) {
                // Single item
                if ($this->matchesSearch($sectionOrItem, $query)) {
                    $results[] = [
                        'title' => $sectionOrItem['title'],
                        'url' => $sectionOrItem['url'] ?? ($sectionOrItem['route'] ?? '#'),
                        'group' => 'System',
                        'icon' => 'cog',
                        'type' => 'menu'
                    ];
                }
            }
        }

        // Add common admin features
        $commonFeatures = [
            ['title' => 'Dashboard', 'url' => route('hyro.admin.dashboard'), 'group' => 'System', 'icon' => 'home'],
            ['title' => 'Plugin Manager', 'url' => route('hyro.admin.plugins'), 'group' => 'System', 'icon' => 'puzzle'],
            ['title' => 'Users', 'url' => '#', 'group' => 'Management', 'icon' => 'users'],
            ['title' => 'Roles', 'url' => '#', 'group' => 'Management', 'icon' => 'shield'],
            ['title' => 'Settings', 'url' => '#', 'group' => 'System', 'icon' => 'cog'],
        ];

        foreach ($commonFeatures as $feature) {
            if (str_contains(strtolower($feature['title']), $query)) {
                $results[] = array_merge($feature, ['type' => 'feature']);
            }
        }

        return array_slice($results, 0, 10); // Limit to 10 results
    }

    protected function matchesSearch($item, $query)
    {
        $title = strtolower($item['title'] ?? '');
        return str_contains($title, $query);
    }

    public function selectResult($url)
    {
        $this->dispatch('navigate', ['url' => $url]);
        $this->reset(['search', 'searchResults', 'showResults']);
    }

    public function render()
    {
        return view('hyro::admin.layouts.partials.header');
    }
}
