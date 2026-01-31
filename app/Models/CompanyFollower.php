<?php

namespace App\Models;

class CompanyFollower extends Model
{
    protected string $table = 'company_followers';
    protected string $primaryKey = 'id';
    protected array $fillable = ['candidate_id', 'company_id'];

    public static function isFollowing($candidateId, $companyId): bool
    {
        return self::where('candidate_id', '=', $candidateId)
            ->where('company_id', '=', $companyId)
            ->first() !== null;
    }

    public static function follow($candidateId, $companyId)
    {
        if (self::isFollowing($candidateId, $companyId)) {
            return false;
        }
        $f = new self();
        $f->fill([
            'candidate_id' => (int)$candidateId,
            'company_id' => (int)$companyId
    
        ]);
        return $f->save();
    }

    public static function unfollow($candidateId, $companyId)
    {
        $row = self::where('candidate_id', '=', $candidateId)
            ->where('company_id', '=', $companyId)
            ->first();
        return $row ? $row->delete() : false;
    }

    public static function countFollowers($companyId): int
{
    $result = self::where('company_id', '=', $companyId)->get();
    return is_array($result) ? count($result) : 0;
}

}
