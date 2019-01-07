<?php

/**
 * Core class used for user notifications.
 *
 * @since 6.0
 *
 * Public methods available:
 * ------------------------
 * add($data)
 * delete($col, $data)
 * render_queued($delete)
 * message($type, $text, $now)
 *
 * $LastChangedDate: 2018-08-15 07:59:04 -0500 (Wed, 15 Aug 2018) $
 * $Rev: 15704 $
 *
 */
class spcNotifications {
	/**
	 * This method queues a new notificaiton entry into the db.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param array $data array of message data matching db table
	 *
	 * @returns bool    true if the notification entry was successful, otherwise false
	 */
	public function add($data) {
		# see if we already have an notice here
		$query         = new stdClass();
		$query->type   = 'var';
		$query->table  = SPNOTICES;
		$query->fields = 'notice_id';
		$query->where  = 'user_id='.$data['user_id'].' AND post_id='.$data['post_id'].' AND message="'.$data['message'].'"';
		$notice        = SP()->DB->select($query);
		if (!empty($notice)) false;

		# create the new notice
		$query         = new stdClass();
		$query->table  = SPNOTICES;
		$query->fields = array('user_id', 'guest_email', 'post_id', 'link', 'link_text', 'message', 'expires');
		$query->data   = array($data['user_id'], $data['guest_email'], $data['post_id'], $data['link'], SP()->saveFilters->title($data['link_text']), SP()->saveFilters->title($data['message']), $data['expires']);
		$query         = apply_filters('sph_new_notice_data', $query);
		$success       = SP()->DB->insert($query);

		return $success;
	}

	/**
	 * This method removes a queued notificaiton entry from the db.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param string $col  column of notification table to match $data to in order to delete entry
	 * @param mixed  $data data to match for deletion
	 *
	 * @returns bool    true if the notification entry was successful, otherwise false
	 */
	public function delete($col, $data) {
		# handle numerica and string data for matcing notification
		$where = (is_numeric($data)) ? "$col = $data" : "$col = '$data'";

		# delete the notification from the db
		$query        = new stdClass();
		$query->table = SPNOTICES;
		$query->where = $where;
		$success      = SP()->DB->delete($query);

		return $success;
	}

	/**
	 * This method renders a queued notificaiton.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param bool $remove whether or not to remove the notification tracking
	 *
	 * @returns void
	 */
	public function render_queued($remove = true) {
		if (SP()->core->status == 'ok' && !SP()->isForumAdmin && isset(SP()->user->thisUser)) {
			# check user boject for queued notifications - if none, check tracking db
			if (!empty(SP()->user->thisUser->notification)) {
				$notification = SP()->user->thisUser->notification;
			} else {
				$query         = new stdClass();
				$query->type   = 'var';
				$query->table  = SPTRACK;
				$query->fields = 'notification';
				$query->where  = 'id='.SP()->user->thisUser->trackid;
				$notification  = SP()->DB->select($query);
			}

			# display any notifications
			if ($notification) {
				# Remove it from sftrack?
				if ($remove) {
					$query         = new stdClass;
					$query->table  = SPTRACK;
					$query->fields = array('notification');
					$query->data   = array('');
					$query->where  = 'id='.SP()->user->thisUser->trackid;
					SP()->DB->update($query);
				}

				# And pass it through to the js for display
				$notification = unserialize($notification);
				apply_filters('sph_queued_notification', $notification[1]);

				# display the message
				?>
                <script>
					(function(spj, $, undefined) {
				        $(document).ready(function () {
					        spj.displayNotification(<?php echo $notification[0]; ?>, '<?php echo esc_js($notification[1]); ?>');
						});
					}(window.spj = window.spj || {}, jQuery));
               </script>
				<?php
			}
		}
	}

	/**
	 * This method handles a user message.  Can be done immediately or queued on tracking.
	 *
	 * @access public
	 *
	 * @since 6.0
	 *
	 * @param int    $type 0 = Success, 1 = Failure, 2 = Wait
	 * @param string $text message to be displayed
	 * @param bool   $now  true = display immediately, false = display on tracking
	 *
	 * @returns void
	 */
	public function message($type, $text, $now = false) {
		# make sure we have track of this user
		if (empty(SP()->user->thisUser->trackid)) return;

		$data = serialize(array($type, $text));

		# send message now or later?
		if ($now) {
			SP()->user->thisUser->notification = $data;
			$this->render_queued(false);
		} else {
			$query         = new stdClass;
			$query->table  = SPTRACK;
			$query->fields = array('notification');
			$query->data   = array($data);
			$query->where  = 'id='.SP()->user->thisUser->trackid;
			SP()->DB->update($query);
		}
	}
}