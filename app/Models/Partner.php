<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Partner extends Model
{
    use HasFactory;

    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several categories for several partners
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_partner')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot('activation_code', 'promo_code', 'number_of_days', 'is_used', 'status_id', 'updated_at');
    }

    /**
     * Check if partner has code
     */
    public function hasPromoCode()
    {
        return $this->categories()->whereNotNull('category_partner.promo_code')->exists();
    }

    /**
     * Get all partner activation codes
     */
    public function allActivationCodes()
    {
        $categories = $this->categories;

        $activationCodes = [];

        foreach ($categories as $category) {
            // Check the activation codes and their status in the pivot relationship
            if ($category->pivot->activation_code) {
                $activationCodes[] = [
                    'activation_code' => $category->pivot->activation_code,
                    'is_used' => $category->pivot->is_used
                ];
            }
        }

        return $activationCodes;
    }

    /**
     * Get all partner activation codes by "is_used"
     */
    public function allActivationCodesByIsUsed($is_used)
    {
        $activationCodes = $this->categories()->wherePivot('is_used', $is_used)
                                ->pluck('pivot.activation_code'); // Retrieve activation codes only

        return $activationCodes;
    }

    /**
     * Calculate the remaining days since the partner's registration.
     *
     * @param \Carbon\Carbon $date
     * @return int
     */
    public function remainingDays($date = null)
    {
        // Utiliser la date actuelle si aucune date n'est fournie
        $date = $date ? Carbon::parse($date) : Carbon::now();

        // Récupérer le dernier enregistrement de l'association catégorie-partenaire
        $pivotData = $this->categories()->latest('updated_at')->first();

        // Vérifier si le partenaire a des catégories associées
        if (!$pivotData) {
            return 'NO PIVOT: ' . 0; // Pas de catégories associées
        }

        // Accéder aux données du pivot
        $pivot = $pivotData->pivot;

        // Vérifier que number_of_days est un nombre valide
        if (!is_numeric($pivot->number_of_days) || $pivot->number_of_days <= 0) {
            return 'PIVOT NOT INT: ' . 0; // Retourner 0 si la durée est invalide
        }

        // Récupérer la date de mise à jour (updated_at)
        $updatedAt = Carbon::parse($pivot->updated_at);
        // Calculer le nombre de jours depuis la mise à jour
        $daysSinceUpdate = $updatedAt->diffInDays($date);
        // Calculer les jours restants
        $remainingDays = $pivot->number_of_days - $daysSinceUpdate;

        // Retourner 0 si les jours restants sont négatifs
        return $remainingDays > 0 ? $remainingDays : 0;
    }
}
