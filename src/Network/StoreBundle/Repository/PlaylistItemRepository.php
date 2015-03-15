<?php

namespace Network\StoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Entity\PlaylistItem;

/**
 * PlaylistItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlaylistItemRepository extends EntityRepository
{
    public function findByPlaylistAndTrack($playlistId, $audioId)
    {
        $em = $this->getEntityManager();
        $dql = "
            SELECT pi from NetworkStoreBundle:PlaylistItem pi
            WHERE pi.playlist = :playlist_id AND pi.audioTrack = :audio_id
        ";

        $query = $em->createQuery($dql)
            ->setParameter('playlist_id', $playlistId)
            ->setParameter('audio_id', $audioId);

        $query->setMaxResults(1);

        return $query->getResult();
    }
}