<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;
    public function reviews () {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle (Builder $query, string $title):Builder {
        return $query->where('title', 'LIKE', '%'. $title . '%' );
    }
    
    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null):Builder {
            return $query->withCount([
                'reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)
            ]);
        }

        public function scopeWithAvgRating(Builder $query, $from = null, $to = null):Builder {
            return $query->withAvg(['reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating');
        }

    public function scopePopular(Builder $query, $from = null, $to = null):Builder {
        return $query->withReviewCount()
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated (Builder $query,  $from = null, $to = null):Builder {
        return $query->withAvgRating()
        ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews (Builder $query, int $minReview):Builder {
        return $query->fromSub($query, 'alias')->where('reviews_count', '>=', $minReview);
    }
    public function scopePopularLastMonth(Builder $query): Builder
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder
    {
        return $query->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }

    protected static function booted () {
        static::updated(fn (Book $book) =>cache()->forget('book:' . $book));
        static::created(fn (Review $review) =>cache()->forget('book:' . $review->book_id));
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null) {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } else if (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } else if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }
}
