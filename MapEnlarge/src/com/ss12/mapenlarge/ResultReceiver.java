package com.ss12.mapenlarge;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.widget.Toast;

public class ResultReceiver extends BroadcastReceiver {
	public static final int RECEIVER_INTENT = 23323211;
	
	@Override
	public void onReceive(Context context, Intent intent) {
		Toast.makeText(context, "BROADCAST RECEIVED!", Toast.LENGTH_LONG).show();
		
		// TODO POLL SERVER AND LAUNCH MAP ACTIVITY

		String map_url = "http://maps.google.com/maps?hl=en&ll=34.06745,-118.44841&spn=0.024636,0.045447&sll=37.0625,-95.677068&sspn=47.885545,93.076172&vpsrc=6&hnear=Strathmore+Dr,+Los+Angeles,+California&t=m&z=15&iwloc=A";

		Intent result_intent = new Intent(context, ResultActivity.class);
		result_intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
		context.startActivity(result_intent);
		
		Intent map_intent = new Intent(android.content.Intent.ACTION_VIEW, Uri.parse(map_url));
		map_intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
		context.startActivity(map_intent);
		
		// TODO ONLY DO THIS IF NEEDED
		/*Intent callback_intent = new Intent(context, ResultReceiver.class);
		PendingIntent pendingIntent = PendingIntent.getBroadcast(context.getApplicationContext(), RECEIVER_INTENT, callback_intent, 0);
		AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
		alarmManager.set(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + 10000, pendingIntent);*/
	}
}
