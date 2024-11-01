<?php

namespace Helious\SeatFAT\Services;

use Seat\Services\Contracts\EsiToken;

class FATEsiToken implements EsiToken
{
    protected string $accessToken;
    protected string $refreshToken;
    protected \DateTime $expiresOn;
    protected array $scopes = [];

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;
        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $token): self
    {
        $this->refreshToken = $token;
        return $this;
    }

    public function getExpiresOn(): \DateTime
    {
        return $this->expiresOn;
    }

    public function setExpiresOn(\DateTime $expires): self
    {
        $this->expiresOn = $expires;
        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes);
    }

    public function isExpired(): bool
    {
        return new \DateTime() > $this->expiresOn;
    }
}
