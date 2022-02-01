<?php 

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ConversationVoter extends Voter
{
    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    const VIEW = 'view';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW])) {
            return false;
        }

        // only vote on `Conversation` objects
        if (!$subject instanceof Conversation) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $result = $this->conversationRepository->checkIfUserIsParticipant($subject->getId(),$token->getUser()->getId());
        
        return !!$result;
        throw new \LogicException('This code should not be reached!');
    }
}