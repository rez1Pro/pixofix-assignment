<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use App\Data\RoleData;

#[TypeScript]
class UserData extends Data
{
  public function __construct(
    public int $id,
    public string $name,
    public string $email,
    public ?string $phone,
    public ?string $email_verified_at,
    public RoleData $role,
  ) {
    //
  }
}
