<?php
use CRM_Qrcodelist_ExtensionUtil as E;

class CRM_Qrcodelist_Page_QRCodeList extends CRM_Core_Page {

  /**
   *  Quick and dirty participant list with QR Code
   */
  public function run() {
    try {
      $eventId = $this->getEventId();
      $dao = $this->getParticipants($eventId);
      $this->printParticipantList($dao);
    }
    catch (Exception $e) {
      echo $e->getMessage();
    }

    parent::run();
  }

  private function getEventId() {
    $eventId = CRM_Utils_Request::retrieveValue('event_id', 'Integer', 0);
    if ($eventId == 0) {
      throw new Exception('Please specify the event ID in the URL: e.g. https://crm.etui.org/civicrm/qrcodelist?event_id=483');
    }

    return $eventId;
  }

  private function getChecksum($participantId, $contactHash) {
    return hash('sha256', $participantId . $contactHash . CIVICRM_SITE_KEY);
  }

  private function printTableHeader() {
    echo '<tr>';
    echo '<td>Participant ID</td>';
    echo '<td>Contact ID</td>';
    echo '<td>Event ID</td>';
    echo '<td>Status ID</td>';
    echo '<td>Display Name</td>';
    echo '<td>QR-Checksum</td>';
    echo '</tr>';
  }

  private function printTableRow($dao) {
    echo '<tr>';
    echo '<td>' . $dao->participant_id . '</td>';
    echo '<td>' . $dao->contact_id . '</td>';
    echo '<td>' . $dao->event_id . '</td>';
    echo '<td>' . $dao->status_id . '</td>';
    echo '<td>' . $dao->display_name . '</td>';
    echo '<td>' . $this->getChecksum($dao->participant_id, $dao->hash) . '</td>';
    echo '</tr>';
  }

  private function printParticipantList($dao) {
    echo '<table>';

    $this->printTableHeader();

    while ($dao->fetch()) {
      $this->printTableRow($dao);
    }

    echo '</table>';
  }

  private function getParticipants($eventId) {
    $sql = "
    select
      p.id participant_id,
      c.id contact_id,
      p.event_id,
      p.status_id,
      c.display_name,
      c.hash
    from
      civicrm_contact c
    inner join
      civicrm_participant p on p.contact_id = c.id
    where
      p.event_id = $eventId
    and
      c.is_deleted = 0
  ";

    return CRM_Core_DAO::executeQuery($sql);
  }

}
