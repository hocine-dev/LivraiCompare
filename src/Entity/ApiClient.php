<?php
// src/Entity/ApiClient.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ApiClientRepository")]
#[ORM\Table(name: "api_client")]
class ApiClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 64, unique: true)]
    private string $apiKey;

    #[ORM\Column(type: "string", length: 180)]
    private string $email;

    #[ORM\Column(type: "string", length: 20)]
    private string $plan; // starter | professional | enterprise

    #[ORM\Column(type: "integer")]
    private int $requestCount = 0;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $subscriptionStart;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $subscriptionEnd;

    #[ORM\Column(type: "boolean")]
    private bool $active = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    public function setRequestCount(int $count): self
    {
        $this->requestCount = $count;
        return $this;
    }

    public function incrementRequestCount(): self
    {
        $this->requestCount++;
        return $this;
    }

    public function getSubscriptionStart(): \DateTimeInterface
    {
        return $this->subscriptionStart;
    }

    public function setSubscriptionStart(\DateTimeInterface $start): self
    {
        $this->subscriptionStart = $start;
        return $this;
    }

    public function getSubscriptionEnd(): \DateTimeInterface
    {
        return $this->subscriptionEnd;
    }

    public function setSubscriptionEnd(\DateTimeInterface $end): self
    {
        $this->subscriptionEnd = $end;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
}
