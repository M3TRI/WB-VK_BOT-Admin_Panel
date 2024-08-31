<?php
namespace Admin;
class User {
    public function __construct(
    public int $subscriptionId,
    public int $userId,
    public string $botName,
    public string $userChannel,
    public \DateTime $subscriptionEndDate,
    public string $subscriptionIssue
    ) {}
}