<?php
/**
 * Node Navigator - Game Data Generator
 * Generates GAME_DATA JSON from portfolio database for the constellation explorer
 */

// Extract about text paragraphs for story fragments
$aboutParagraphs = array_filter(array_map('trim', explode("\n", $about)));
$storyFragments = array_slice($aboutParagraphs, 0, 3);

// Build projects array for game
$gameProjects = array_map(function($p) {
    return [
        'id' => 'proj-' . $p['id'],
        'title' => $p['title'],
        'description' => $p['description'] ?? '',
        'image' => $p['image'] ?? '',
        'github' => $p['github_url'] ?? '',
        'live' => $p['public_url'] ?? '',
        'tags' => array_filter([$p['tag1'] ?? '', $p['tag2'] ?? ''])
    ];
}, $projects);

// Build skills array for game
$gameSkills = array_map(function($s) {
    return [
        'id' => 'skill-' . $s['id'],
        'name' => $s['name'],
        'icon' => $s['icon'] ?? ''
    ];
}, $skills);

// Build social links array
$gameSocials = array_values(array_filter([
    !empty($settings['github_url']) ? ['id' => 'social-gh', 'name' => 'GitHub', 'url' => $settings['github_url'], 'icon' => 'fab fa-github'] : null,
    !empty($settings['linkedin_url']) ? ['id' => 'social-li', 'name' => 'LinkedIn', 'url' => $settings['linkedin_url'], 'icon' => 'fab fa-linkedin'] : null,
    !empty($settings['instagram_url']) ? ['id' => 'social-ig', 'name' => 'Instagram', 'url' => $settings['instagram_url'], 'icon' => 'fab fa-instagram'] : null,
    !empty($settings['facebook_url']) ? ['id' => 'social-fb', 'name' => 'Facebook', 'url' => $settings['facebook_url'], 'icon' => 'fab fa-facebook'] : null,
]));

// Build story fragments
$gameStories = [];
foreach ($storyFragments as $i => $fragment) {
    $gameStories[] = [
        'id' => 'story-' . $i,
        'fragment' => $fragment,
        'title' => $i === 0 ? 'Introduction' : ($i === 1 ? 'Journey' : 'Philosophy')
    ];
}

// Calculate total discoverable nodes
$totalNodes = count($gameProjects) + count($gameSkills) + count($gameStories) + count($gameSocials);

// Build complete game data structure
$gameData = [
    'core' => [
        'id' => 'zenith',
        'label' => 'ZENITH',
        'name' => $name,
        'role' => $role,
        'location' => $location ?? ''
    ],
    'sections' => [
        [
            'id' => 'work',
            'label' => 'PROJECTS',
            'icon' => 'fas fa-code',
            'orbit' => 1,
            'angle' => 0,
            'children' => $gameProjects
        ],
        [
            'id' => 'skills',
            'label' => 'TECH',
            'icon' => 'fas fa-tools',
            'orbit' => 1,
            'angle' => 120,
            'children' => $gameSkills
        ],
        [
            'id' => 'about',
            'label' => 'STORY',
            'icon' => 'fas fa-user',
            'orbit' => 1,
            'angle' => 240,
            'children' => $gameStories
        ],
        [
            'id' => 'contact',
            'label' => 'CONNECT',
            'icon' => 'fas fa-envelope',
            'orbit' => 2,
            'angle' => 60,
            'action' => 'scrollTo',
            'target' => '#contact',
            'children' => []
        ],
        [
            'id' => 'social',
            'label' => 'LINKS',
            'icon' => 'fas fa-link',
            'orbit' => 2,
            'angle' => 180,
            'children' => $gameSocials
        ]
    ],
    'meta' => [
        'totalNodes' => $totalNodes
    ]
];
?>
<script>
window.GAME_DATA = <?= json_encode($gameData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>;
</script>
