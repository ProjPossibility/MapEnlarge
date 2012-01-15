package com.ss12.mapenlarge;

import java.io.IOException;
import java.util.HashMap;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.net.Uri;
import android.util.Log;

public class ResultReceiver extends BroadcastReceiver {
	public static final int RECEIVER_INTENT = 23323211;
	
	@Override
	public void onReceive(Context context, Intent intent) {
		//Toast.makeText(context, "BROADCAST RECEIVED!", Toast.LENGTH_LONG).show();
		
		HashMap<String, String> map = new HashMap<String, String>();
		map.put(context.getResources().getString(R.string.web_service_uuid), Global.uuid);
		
		JSONObject resp = null;
		try {
			try {
				resp = HttpHelper.get(context.getResources().getString(R.string.web_service_check_url), context.getResources().getString(R.string.web_service_uuid) + "=" + Global.uuid);
				Log.i("response", resp.toString());
			} catch (NullPointerException e) {
				e.printStackTrace();
			} catch (NotFoundException e) {
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			}
		
			if (resp != null && resp.getBoolean("status")) {
				// If status is true, we can get the link
				String map_url = resp.getString("link");
				
				Intent result_intent = new Intent(context, ResultActivity.class);
				result_intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
				context.startActivity(result_intent);
				
				Intent map_intent = new Intent(android.content.Intent.ACTION_VIEW, Uri.parse(map_url));
				map_intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
				context.startActivity(map_intent);
			} else {
				// If the status is false, register another callback
				Intent callback_intent = new Intent(context, ResultReceiver.class);
				callback_intent.putExtras(intent);
				PendingIntent pendingIntent = PendingIntent.getBroadcast(context.getApplicationContext(), RECEIVER_INTENT, callback_intent, 0);
				AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
				alarmManager.set(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + 10000, pendingIntent);
			}
		} catch (JSONException e) {
			e.printStackTrace();
		}
	}
}
