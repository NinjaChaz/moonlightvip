<?php
require_once '../includes/functions.php';

// Get all series (filter adult content if safe mode is enabled)
$safe_mode = getUserPreference('safe_mode');
$stmt = $pdo->prepare("SELECT * FROM series WHERE is_adult = FALSE OR ? = FALSE ORDER BY title_en");
$stmt->execute([$safe_mode]);
$all_series = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <h2><?php echo getTranslation('All Series', 'စီးရီးအားလုံး'); ?></h2>
    
    <!-- Search and Filter -->
    <div class="search-filter">
        <input type="text" id="series-search" placeholder="<?php echo getTranslation('Search series...', 'စီးရီးများ ရှာဖွေရန်...'); ?>" class="search-input">
        <select id="series-sort">
            <option value="title"><?php echo getTranslation('Title (A-Z)', 'ခေါင်းစဉ် (က-အ)'); ?></option>
            <option value="newest"><?php echo getTranslation('Newest First', 'နောက်ဆုံးထုတ်စဉ်'); ?></option>
            <option value="oldest"><?php echo getTranslation('Oldest First', 'ရှေးဦးထုတ်စဉ်'); ?></option>
        </select>
    </div>
    
    <!-- Series Grid -->
    <div class="series-grid" id="series-container">
        <?php foreach ($all_series as $series): ?>
        <div class="series-card" data-title="<?php echo strtolower(getTranslation($series['title_en'], $series['title_my'])); ?>" data-date="<?php echo $series['created_at']; ?>">
            <a href="chapter.php?series=<?php echo $series['id']; ?>">
                <img src="../uploads/covers/<?php echo $series['cover_image']; ?>" alt="<?php echo getTranslation($series['title_en'], $series['title_my']); ?>" class="series-cover">
            </a>
            <div class="series-info">
                <a href="chapter.php?series=<?php echo $series['id']; ?>" class="series-title">
                    <?php echo getTranslation($series['title_en'], $series['title_my']); ?>
                </a>
                <div class="series-description">
                    <?php 
                    $description = getTranslation($series['description_en'], $series['description_my']);
                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                    ?>
                </div>
                <div class="chapter-count">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chapters WHERE series_id = ?");
                    $stmt->execute([$series['id']]);
                    $count = $stmt->fetchColumn();
                    echo $count . ' ' . getTranslation('Chapters', 'အခန်းများ');
                    ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($all_series)): ?>
    <div class="no-results">
        <p><?php echo getTranslation('No series found.', 'စီးရီးများ မတွေ့ရှိပါ။'); ?></p>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('series-search');
    const sortSelect = document.getElementById('series-sort');
    const seriesContainer = document.getElementById('series-container');
    const seriesCards = Array.from(seriesContainer.getElementsByClassName('series-card'));
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        seriesCards.forEach(card => {
            const title = card.getAttribute('data-title');
            if (title.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Sort functionality
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        
        // Convert to array for sorting
        const sortedCards = [...seriesCards];
        
        if (sortBy === 'title') {
            sortedCards.sort((a, b) => {
                const titleA = a.getAttribute('data-title');
                const titleB = b.getAttribute('data-title');
                return titleA.localeCompare(titleB);
            });
        } else if (sortBy === 'newest') {
            sortedCards.sort((a, b) => {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return dateB - dateA;
            });
        } else if (sortBy === 'oldest') {
            sortedCards.sort((a, b) => {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return dateA - dateB;
            });
        }
        
        // Clear container and append sorted cards
        seriesContainer.innerHTML = '';
        sortedCards.forEach(card => {
            seriesContainer.appendChild(card);
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>