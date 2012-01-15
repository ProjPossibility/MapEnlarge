package com.ss12.mapenlarge;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.HashMap;
import java.util.UUID;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.app.AlarmManager;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Bitmap.CompressFormat;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Base64;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.Toast;

public class ImageCaptureActivity extends Activity {
	private static final int TAKE_PICTURE = 0;
	
	Button btn_capture_image;
	Button btn_submit;
	ImageView iv_preview;
	
	ProgressDialog pd;
	
	Uri imageUri;
	
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.page_image_capture);
        
        // Init UI
        btn_capture_image = (Button) findViewById(R.id.btn_capture_image);
        btn_capture_image.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
			    Intent intent = new Intent("android.media.action.IMAGE_CAPTURE");
			    File photo = new File(Environment.getExternalStorageDirectory(),  "Pic.jpg");
			    intent.putExtra(MediaStore.EXTRA_OUTPUT,
			            Uri.fromFile(photo));
			    imageUri = Uri.fromFile(photo);
			    startActivityForResult(intent, TAKE_PICTURE);

			}
		});
        
        btn_submit = (Button) findViewById(R.id.btn_submit);
        btn_submit.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				// TODO SUBMIT
				SubmitTask submit_task = new SubmitTask();
				submit_task.execute((Void[]) null);
			}
		});
        
        iv_preview = (ImageView) findViewById(R.id.iv_preview);
    }

	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		
	    switch (requestCode) {
	    case TAKE_PICTURE:
	        if (resultCode == Activity.RESULT_OK) {
	            Uri selectedImage = imageUri;
	            getContentResolver().notifyChange(selectedImage, null);
	            ImageView imageView = (ImageView) findViewById(R.id.iv_preview);
	            ContentResolver cr = getContentResolver();
	            Bitmap bitmap;
	            try {
	                bitmap = android.provider.MediaStore.Images.Media.getBitmap(cr, selectedImage); 

	                imageView.setImageBitmap(bitmap);
	                
	                bitmap = null;
	                //Toast.makeText(this, selectedImage.toString(), Toast.LENGTH_LONG).show();
	            } catch (Exception e) {
	                Toast.makeText(this, "Failed to load", Toast.LENGTH_SHORT).show();
	                Log.e("Camera", e.toString());
	            }
	        }
	    }
	}
	
	private class SubmitTask extends AsyncTask<Void, Void, Boolean> {
		@Override
		protected void onPreExecute() {
			pd = new ProgressDialog(ImageCaptureActivity.this);
			pd.setMessage("Submitting image...");
			
			pd.show();
		}
		
		@Override
		protected Boolean doInBackground(Void... params) {
			String uuid = UUID.randomUUID().toString();
			String image_url = "";
			
			Bitmap bitmap;
			JSONObject resp;
			try {
				bitmap = android.provider.MediaStore.Images.Media.getBitmap(getContentResolver(), imageUri);
			
				// Creates Byte Array from picture
				ByteArrayOutputStream baos = new ByteArrayOutputStream();
				bitmap.compress(CompressFormat.JPEG, 100, baos);
	
				// Send to imgur
				HashMap<String, String> map = new HashMap<String, String>();
				map.put("image", Base64.encodeToString(baos.toByteArray(), Base64.DEFAULT));
				map.put("key", "78959dfe8fe6b1be08d0eb3f50ea156c");
				
				baos.flush();
				baos.close();
				
				bitmap.recycle();

				resp = HttpHelper.post("http://api.imgur.com/2/upload.json", map, null, null);
				image_url = resp.getJSONObject("upload").getJSONObject("links").getString("original");
				
				
				// Send to our server
				map.clear();
				map.put(getResources().getString(R.string.web_service_uuid), uuid);
				map.put(getResources().getString(R.string.web_service_image), image_url);
				
				resp = HttpHelper.post(getResources().getString(R.string.web_service_submit_url), map, null, null);
				
				if (!resp.getBoolean("status"))
					return false;
				else
					Global.uuid = uuid;
				
				Log.i("response", resp.toString());
			} catch (FileNotFoundException e) {
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			} catch (NullPointerException e) {
				e.printStackTrace();
			} catch (JSONException e) {
				e.printStackTrace();
			}
		
			// TODO DO SOMETHING WITH RESULT
			
			Intent i = new Intent(ImageCaptureActivity.this, ResultReceiver.class);
			PendingIntent pendingIntent = PendingIntent.getBroadcast(getApplicationContext(), ResultReceiver.RECEIVER_INTENT, i, 0);
			AlarmManager alarmManager = (AlarmManager) getSystemService(Context.ALARM_SERVICE);
			alarmManager.set(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + 10000, pendingIntent);
			
			return true;
		}
		
		@Override
		protected void onPostExecute(Boolean result) {
			super.onPostExecute(result);
			
			pd.dismiss();
			
			if (result)
				Toast.makeText(ImageCaptureActivity.this, "Submission Successful!  You will be notified when a result is found.", Toast.LENGTH_LONG).show();
			else
				Toast.makeText(ImageCaptureActivity.this, "Submission Unsuccessful.  Please try again.", Toast.LENGTH_LONG).show();
		}
	}
}