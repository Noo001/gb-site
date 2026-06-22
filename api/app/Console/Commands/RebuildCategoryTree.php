<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildCategoryTree extends Command
{
    protected $signature = 'rebuild:category-tree {--dry-run : Show changes without saving}';

    protected $description = 'Rebuild parent_id links from category full_path values';

    public function handle(): int
    {
        $categories = Category::query()
            ->orderByRaw("(length(full_path) - length(replace(full_path, '/', ''))) asc")
            ->get();

        $paths = $categories->keyBy('full_path');

        $bar = $this->output->createProgressBar($categories->count());

        $updated = 0;
        $skipped = 0;

        foreach ($categories as $category) {
            $segments = array_filter(explode('/', trim($category->full_path, '/')));

            // Root categories (e.g. /catalog/apple/ or /brands/apple/) have no parent.
            if (count($segments) <= 2) {
                if ($category->parent_id !== null) {
                    if (! $this->option('dry-run')) {
                        $category->update(['parent_id' => null]);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
                $bar->advance();
                continue;
            }

            $parentSegments = array_slice($segments, 0, -1);
            $parentPath = '/'.implode('/', $parentSegments).'/'; // e.g. /catalog/gadzhety/

            $parent = $paths->get($parentPath);

            if (! $parent) {
                $this->warn("Parent not found for {$category->full_path} (expected {$parentPath})");
                $bar->advance();
                continue;
            }

            if ($category->parent_id !== $parent->id) {
                if (! $this->option('dry-run')) {
                    $category->update(['parent_id' => $parent->id]);
                }
                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $action = $this->option('dry-run') ? 'Would update' : 'Updated';
        $this->info("{$action} {$updated} categories, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
