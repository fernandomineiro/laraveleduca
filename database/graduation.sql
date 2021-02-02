create table cursos_faculdades(
  id INT AUTO_INCREMENT,
  profile_id      int NOT NULL,
  course_id       int NOT NULL,
  start_date      DATE,
  final_date      DATE,
  PRIMARY KEY (id)
);
